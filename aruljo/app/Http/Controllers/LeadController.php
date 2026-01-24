<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadProductMap;
use App\Models\Quotation\Quotation;
use App\Models\Quotation\QuoteVersion;
use App\Models\User;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Tags\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DistanceController;
use Illuminate\Support\Facades\Log;
use App\Services\QuoteCalculatorService;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    /**
     * Display create lead form.
     */
    public function create()
    {
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        $tags = Tag::all();
        $platforms = config('platforms.list');
        // Remove "Justdial" only in create
        $platforms = array_filter($platforms, fn($p) => $p !== 'Justdial');

        // Fetch all products for autocomplete
        $products = Product::all();
        $productsArray = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'weight' => $p->weight_kg,
            'price' => $p->quote_price,
        ])->toArray();

        return view('leads.create', compact('users', 'tags', 'platforms', 'productsArray'));
    }

    /**
     * Store a newly created lead.
     */

    public function store(Request $request)
        {
            $validated = $request->validate([
                'platform' => 'required|string',
                'lead_date' => 'required|date',
                'buyer_name' => 'required|string',
                'buyer_location' => 'nullable|string',
                'buyer_contact' => ['required', 'regex:/^[6-9]\d{9}$/'],
                'platform_keyword' => 'nullable|string',
                'product_detail' => 'nullable|string',
                'delivery_location_id' => 'nullable|exists:distance_pincodes,id',
                'expected_delivery_date' => 'nullable|date|after_or_equal:today',
                'follow_up_date' => 'nullable|date|after_or_equal:today',
                'status' => ['required', Rule::in($this->allowedStatuses())],
                'assigned_to' => 'nullable|string',
                'current_remark' => 'nullable|string|max:500',
                'tags' => 'nullable|array',
                'tags.*' => 'string',
            ]);

            $validated['assigned_to'] = $validated['assigned_to'] ?? Auth::user()->name;
            $user = Auth::user()->name;
            $timestamp = now()->format('d M Y, h:i A');

            $remarkText = ($validated['assigned_to'] !== $user) ? "assigned to {$validated['assigned_to']}" : "created the lead";
            if ($request->filled('current_remark')) $remarkText .= " — {$request->current_remark}";

            $lead = new Lead(collect($validated)->except(['current_remark', 'tags', 'product_detail'])->toArray());
            $lead->remarks = "{$user} ({$timestamp}): {$remarkText}";
            $lead->save();

            // Sync tags
            if ($request->filled('tags')) {
                $validTags = Tag::whereIn('name->en', $request->tags)->get();
                $lead->syncTags($validTags);
            }

            // Handle product mapping
            if ($request->filled('product_detail')) {
                $products = explode('~|~', $request->product_detail);
                $allProductsWithQty = [];
                foreach ($products as $prod) {
                    $parts = explode(',', $prod);
                    $name = trim($parts[0]);
                    $qty = isset($parts[1]) ? (int)trim($parts[1]) : 1;

                    $product = Product::where('name', $name)->first();
                    if ($product) {
                        $lead->products()->attach($product->id, ['quantity' => $qty]);
                        $allProductsWithQty[] = "{$name},{$qty}";
                    }
                }
                $lead->product_detail = implode('~|~', $allProductsWithQty);
                $lead->save();
            }

            // ---------------- Update Distance Cache ----------------
            $toId = $request->delivery_location_id ?? null;
            $submittedDistance = $request->distance_km ?? null;
            $submittedDuration = $request->duration_minutes ?? null;

            if ($toId && $submittedDistance !== null && $submittedDuration !== null) {
                $cache = \App\Models\DistanceCache::firstOrNew([
                    'to_location_id' => $toId,
                ]);

                if ($cache->distance_km !== (int)$submittedDistance || $cache->duration_minutes !== (int)$submittedDuration) {
                    $cache->distance_km = (int)$submittedDistance;
                    $cache->duration_minutes = (int)$submittedDuration;
                    $cache->user_update = true;
                    $cache->last_updated = now();
                    $cache->save();
                }
            }

            // Redirect based on which button was pressed
            if ($request->submit_action === 'quote') {
                return redirect()->route('quotations.create', ['lead_id' => $lead->id])
                    ->with('success', 'Lead created successfully! You can now create a quotation.');
            }

            return redirect()->route('leads.index')->with('success', 'Lead created successfully!');
        }

    /**
     * Display a listing of the leads.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'active');
        $today = now()->toDateString();

        // euser default tab
        if (
            ($user->getRoleNames()->count() === 1 && $user->hasRole('euser')) ||
            $user->getRoleNames()->count() === 0
        ) {
            $tab = 'my';
        }

        // ✅ AJAX path — DataTables
        if ($request->ajax()) {
            $query = Lead::with('tags');

            // Filter by tab
            if ($tab === 'all') {
                $query->where('created_at', '>=', now()->subDays(60));
            } elseif ($tab === 'my') {
                $query->where('assigned_to', $user->name)
                      ->whereNotIn('status', ['Cancelled', 'Completed']);
            } elseif ($tab === 'active') {
                $query->whereNotIn('status', ['Cancelled', 'Completed'])
                      ->where(function ($q) use ($today) {
                          $q->whereNull('follow_up_date')
                            ->orWhere('follow_up_date', '<=', $today);
                      });
            }

            // Sorting logic for urgency & follow-up
            $query->select('leads.*')->selectRaw("
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM taggables tg
                        JOIN tags t ON t.id = tg.tag_id
                        WHERE tg.taggable_id = leads.id
                          AND tg.taggable_type = ?
                          AND JSON_UNQUOTE(JSON_EXTRACT(t.name, '$.en')) = 'Urgent'
                    ) THEN 1
                    WHEN DATE(follow_up_date) = ? THEN 2
                    WHEN follow_up_date IS NOT NULL AND DATE(follow_up_date) < ? THEN 3
                    ELSE 4
                END as sort_priority
            ", [Lead::class, $today, $today]);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('platform', fn($lead) => $tab === 'all' ? e($lead->platform) : null)
                ->addColumn('buyer', function ($lead) {
                    $tags = $lead->tags->map(fn($t) => '<span class="badge badge-info">'.$t->name.'</span>')
                                      ->implode(' ');
                    $name = Str::limit(e($lead->buyer_name), 20);
                    return <<<HTML
                        <a href="javascript:void(0);" class="open-edit-lead-modal" data-lead-id="{$lead->id}">
                            {$tags} <span title="{$lead->buyer_name}">{$name}</span>
                        </a>
                    HTML;
                })
                ->addColumn('lead_date', function ($lead) {
                    if (!$lead->lead_date) return '<span class="text-muted">—</span>';
                    $d = Carbon::parse($lead->lead_date);
                    $short = $d->format('d-m-Y');
                    $full = $d->format('d-m-Y h:i A');
                    $daysago = str_pad($d->diffInDays(now()), 2, '0', STR_PAD_LEFT);
                    return "<span title='{$full}'>{$short} <small class='text-muted'>({$daysago})</small></span>";
                })
                ->addColumn('buyer_contact', function ($lead) {
                    if (!$lead->buyer_contact) return '-';
                    $phone = e($lead->buyer_contact);
                    $wa = "https://wa.me/91{$phone}";
                    return <<<HTML
                        <a href="tel:{$phone}" onclick="copyPhone(event, '{$phone}')" class="text-primary">
                            {$phone}
                        </a>
                        <a href="{$wa}" target="_blank" class="ms-2">
                            <button class="btn btn-success btn-xs"><i class="fab fa-whatsapp"></i></button>
                        </a>
                    HTML;
                })
                ->addColumn('follow_up_date', function ($lead) {
                    if (!$lead->follow_up_date) return '-';
                    $f = Carbon::parse($lead->follow_up_date);
                    $formatted = $f->format('d-m-Y');
                    $cls = $f->isToday()
                        ? 'bg-warning text-dark px-2 py-1 rounded'
                        : ($f->isPast() ? 'bg-danger text-white px-2 py-1 rounded' : '');
                    return "<span class='{$cls}'>{$formatted}</span>";
                })
                ->addColumn('actions', function ($lead) {
                    $logUrl = route('leads.audits', $lead->id);
                    $deleteUrl = route('leads.destroy', $lead->id);
                    return <<<HTML
                        <div class="d-flex align-items-center">
                            <a href="{$logUrl}" class="btn btn-xs btn-outline-info ml-1" title="View Logs">
                                <i class="fas fa-sticky-note"></i>
                            </a>
                            <i class="fas fa-trash text-danger ml-2" style="cursor:pointer; font-size:0.85rem;"
                                data-toggle="modal" data-target="#deleteModal"
                                onclick="setDeleteAction('{$deleteUrl}')"></i>
                        </div>
                    HTML;
                })
                ->rawColumns(['buyer', 'buyer_contact', 'lead_date', 'follow_up_date', 'actions'])
                ->make(true);
        }

        // ---------------- Normal view load ----------------
        $users = User::whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))->get();
        $statuses = $this->allowedStatuses();
        $platforms = config('platforms.list');
        $allTags = Tag::pluck('name');
        $isEuser = ($user->getRoleNames()->count() === 0)
            || ($user->getRoleNames()->count() === 1 && $user->hasRole('euser'));

        $productsArray = Product::all()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'weight' => $p->weight_kg,
            'price' => $p->quote_price,
        ])->toArray();

        return view('leads.index', compact(
            'users', 'tab', 'statuses', 'platforms', 'allTags', 'isEuser', 'productsArray'
        ));
    }



    /**
     * Show lead for editing.
     */
    public function edit(Lead $lead)
    {
        $locationId = $lead->delivery_location_id;

        $distance_km = null;
        $duration_minutes = null;
        $pincode = null;
        $fullLocation = null;

        if ($locationId) {
            $location = $lead->location;

            if ($location) {
                $pincode = $location->pincode;
                $fullLocation = $location->full_location . '-' . $pincode;

                $cache = $location->latestCache;

                if ($cache) {
                    $distance_km = round($cache->distance_km);
                    $duration_minutes = round($cache->duration_minutes);
                }
            }
        }

        // 🔹 Fetch quotation linked to this lead (if any)
        $quotation = Quotation::where('lead_id', $lead->id)
            ->latest('id')
            ->first();

        // 🔹 Only check versions if quotation exists
        $versionId = $quotation
            ? optional($quotation->versions()->latest('id')->first())->id
            : null;

        return response()->json([
            'id' => $lead->id,
            'buyer_name' => $lead->buyer_name,
            'buyer_contact' => $lead->buyer_contact,
            'lead_date' => $lead->lead_date,
            'platform' => $lead->platform,
            'platform_keyword' => $lead->platform_keyword,
            'product_detail' => $lead->product_detail,
            'buyer_location' => $lead->buyer_location,
            'pincode' => $pincode,
            'delivery_location_id' => $locationId,
            'distance_km' => $distance_km,
            'duration_minutes' => $duration_minutes,
            'delivery_location' => $fullLocation,
            'expected_delivery_date' => $lead->expected_delivery_date,
            'follow_up_date' => $lead->follow_up_date,
            'status' => $lead->status,
            'assigned_to' => $lead->assigned_to,
            'current_remark' => '',
            'past_remarks' => explode('~|~', $lead->remarks ?? ''),
            'tags' => $lead->tags->pluck('name')->toArray(),
            'quotation_id' => $quotation?->id,
            'version_id'   => $versionId,
        ]);
    }


    /**
     * Update lead details.
     */
    public function update(Request $request, $id)
        {
            $lead = Lead::findOrFail($id);
            $validated = $request->validate([
                'platform' => 'required|string',
                'lead_date' => 'required|date',
                'buyer_name' => 'required|string',
                'buyer_location' => 'nullable|string',
                'buyer_contact' => ['required', 'regex:/^[6-9]\d{9}$/'],
                'platform_keyword' => 'nullable|string',
                'product_detail' => 'nullable|string',
                'delivery_location_id' => 'nullable|exists:distance_pincodes,id',
                'expected_delivery_date' => 'nullable|date|after_or_equal:today',
                'follow_up_date' => 'nullable|date|after_or_equal:today',
                'status' => ['required', Rule::in($this->allowedStatuses())],
                'assigned_to' => 'nullable|string',
                'current_remark' => 'nullable|string|max:500',
                'tags' => 'nullable|array',
                'tags.*' => 'string',
            ]);

            $oldAssignedTo = $lead->assigned_to;
            $lead->fill(collect($validated)->except(['current_remark', 'tags'])->toArray());

            $user = Auth::user()->name;
            $timestamp = now()->format('d M Y, h:i A');
            $remarkText = null;

            if ($oldAssignedTo !== $lead->assigned_to && $lead->assigned_to) {
                $remarkText = "reassigned to {$lead->assigned_to}";
            }

            if ($request->filled('current_remark')) {
                $remarkText = $remarkText ? "{$remarkText} — {$request->current_remark}" : $request->current_remark;
            }

            if ($remarkText) {
                $fullRemark = "{$user} ({$timestamp}): {$remarkText}";
                $lead->remarks = $lead->remarks ? $fullRemark . "~|~" . $lead->remarks : $fullRemark;
            }

            $lead->save();

            // Sync tags
            $validTags = collect($request->input('tags', []))
                ->filter()
                ->map(fn($tagName) => Tag::where('name->en', $tagName)->first())
                ->filter()
                ->values();
            $lead->syncTags($validTags);

            // ---------------- Update Distance Cache ----------------
            $toId = $request->delivery_location_id ?? null;
            $submittedDistance = $request->distance_km ?? null;
            $submittedDuration = $request->duration_minutes ?? null;

            if ($toId && $submittedDistance !== null && $submittedDuration !== null) {
                $cache = \App\Models\DistanceCache::firstOrNew([
                    'to_location_id' => $toId,
                ]);

                if ($cache->distance_km !== (int)$submittedDistance || $cache->duration_minutes !== (int)$submittedDuration) {
                    $cache->distance_km = (int)$submittedDistance;
                    $cache->duration_minutes = (int)$submittedDuration;
                    $cache->user_update = true;
                    $cache->last_updated = now();
                    $cache->save();
                }
            }

            // Redirect based on which button was pressed
            if ($request->submit_action === 'quote') {
                $existingQuotation = Quotation::where('lead_id', $lead->id)->latest('id')->first();

                if ($existingQuotation) {
                    $redirectUrl = route('quotations.create-version', ['id' => $existingQuotation->id]);
                } else {
                    $redirectUrl = route('quotations.create', ['lead_id' => $lead->id]);
                }

                // ✅ If AJAX call, return JSON instead of redirect()
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'redirect_to' => $redirectUrl,
                    ]);
                }

                // Otherwise, allow normal redirect for non-AJAX forms
                return redirect($redirectUrl)
                    ->with('success', 'Lead updated successfully! Proceed to quotation.');
            }


            $tab = $request->input('tab', 'active');
            return redirect()->route('leads.index', $tab === 'active' ? [] : ['tab' => $tab])
                ->with('success', 'Lead updated successfully.');

        }


    /**
     * Delete a lead.
     */
    public function destroy($id)
    {
        Lead::findOrFail($id)->delete();

        return redirect()->route('leads.index')->with('success', 'Lead moved to trash.');
    }

    /**
     * Show audit logs for a lead.
     */
   public function showAudits($id)
   {
       $lead = Lead::findOrFail($id);
       $user = auth()->user();

       // ✅ Allow admins and owners regardless of euser role
       if ($user->hasAnyRole(['admin', 'owner'])) {
           $audits = $lead->audits()->latest()->get();
           return view('leads.audits', compact('lead', 'audits'));
       }

       // 🔒 Restrict euser to only their assigned leads
       if ($lead->assigned_to !== $user->name) {
           abort(403, 'Unauthorized access to this audit log.');
       }

       $audits = $lead->audits()->latest()->get();
       return view('leads.audits', compact('lead', 'audits'));
   }


    /**
     * Allowed statuses.
     */
    private function allowedStatuses(): array
    {
        return [
            'New Lead',
            'Lead Followup',
            'Quotation',
            'PO',
            'Cancelled',
            'Completed',
        ];
    }


    private function formatDaysDiff($date)
    {
        if (!$date) {
            return '';
        }

        $today = Carbon::today();
        $date = Carbon::parse($date)->startOfDay();

        $diff = $date->diffInDays($today, false); // negative if in future

        if ($diff === 0) {
            return 'Today';
        } elseif ($diff > 0) {
            return "{$diff} day(s) ago";
        } else {
            return abs($diff) . " day(s) from today";
        }

    }

    public function calculateDraftQuote(Request $request, QuoteCalculatorService $calculator)
        {
            $productInputs = $request->input('products', []);
            $distance = (float) $request->input('distance_km', 0);
            $locationId = $request->input('delivery_location_id');

            $productIds = collect($productInputs)->pluck('id')->filter()->all();

            $products = \App\Models\Product\Product::with([
                    'template',
                    'parameterValues.parameter'
                ])
                ->whereIn('id', $productIds)
                ->get()
                ->map(function ($product) use ($productInputs) {
                    $reqItem = collect($productInputs)->firstWhere('id', $product->id);

                    // Attach qty and price (from request)
                    $product->qty = $reqItem['qty'] ?? 0;
                    $product->price = $reqItem['price'] ?? $product->quote_price;
                    $product->weight = $reqItem['weight'] ?? $product->weight_kg;

                    // Replace parameter IDs with readable names
                    $product->parameterValues = $product->parameterValues->map(function ($pv) {
                        return [
                            'parameter' => $pv->parameter->name ?? 'Unknown',
                            'value'     => $pv->value,
                        ];
                    });
                    return $product;
                });


            $result = $calculator->calculateByCapacity($products->toArray(), $distance, $locationId);

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 400);
            }

            return response()->json($result);
        }


    public function export()
    {
        return Excel::download(new LeadsExport, 'leads.xlsx');
    }

    public function checkDuplicate(Request $request)
    {
        $number = trim($request->input('buyer_contact'));
        $exists = Lead::where('buyer_contact', $number)
            ->where('created_at', '>=', Carbon::now()->subDays(3))
            ->exists();

        return response()->json(['exists' => $exists]);
    }
    public function getQuoteReferenceData(Request $request)
    {
        $products    = $request->input('products', []);
        $distance    = (float) $request->input('distance_km', 0);
        $locationId  = $request->input('delivery_location_id');
        $includeDraft = filter_var($request->input('include_draft', false), FILTER_VALIDATE_BOOLEAN);

        $service = new \App\Services\QuoteCalculatorService();

        return response()->json(
            $service->getQuoteReferenceData($products, $distance, $locationId, $includeDraft)
        );
    }

}
