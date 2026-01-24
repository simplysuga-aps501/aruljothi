<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

use App\Models\Quotation\Quotation;
use App\Models\Quotation\QuoteVersion;
use App\Models\Quotation\QuoteTruck;
use App\Models\Quotation\QuoteTruckProduct;
use App\Models\Quotation\QuotePriceDetail;
use App\Models\Quotation\QuoteAdditionalField;

use App\Models\Product\Product;
use App\Models\Lead;
use App\Models\Transport\TruckType;
use App\Models\Transport\TpDistrictRate;
use App\Models\Transport\TpOffice;
use App\Models\Transport\TruckCapacity;
use App\Models\Transport\TpKmMultiplier;
use App\Models\Customer;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Quotation::with(['lead', 'creator', 'modifier', 'activeVersion'])
                ->whereHas('lead', function ($q) {
                    $q->where('status', '!=', 'Cancelled');
                })
                ->latest();

            return DataTables::of($query)
                ->addColumn('lead_no', fn($q) => $q->lead->id ?? '-')
                ->addColumn('buyer_name', function ($q) {
                    if (!$q->lead) return '-';
                    $url = route('quotations.create-version', $q->id);
                    return '<a href="'.$url.'">'.$q->lead->buyer_name.'</a>';
                })
                ->addColumn('contact', function ($q) {
                    if (!$q->lead || !$q->lead->buyer_contact) return '-';
                    $contact = preg_replace('/\D/', '', $q->lead->buyer_contact);
                    if (strlen($contact) == 10) $contact = '91' . $contact;
                    $whatsappUrl = "https://wa.me/{$contact}";
                    $callUrl = "tel:+{$contact}";
                    return '
                        <a href="'.$callUrl.'" class="text-primary" title="Click to call">'
                            .$q->lead->buyer_contact.'</a>
                        <a href="'.$whatsappUrl.'" target="_blank" class="text-success ml-2" title="Chat on WhatsApp">
                            <i class="fab fa-whatsapp fa-lg"></i>
                        </a>
                    ';
                })
                ->addColumn('amount', fn($q) => formatIndianCurrency($q->total_amount))
                ->addColumn('modified_by', fn($q) => $q->modifier->name ?? $q->creator->name ?? '-')
                ->addColumn('last_updated', fn($q) => $q->updated_at?->format('d-M-Y H:i'))
                ->addColumn('actions', function ($q) {
                    return '<button class="btn btn-xs btn-danger download-pdf" data-id="'.$q->id.'">
                                <i class="fas fa-file-pdf"></i>
                            </button>';
                })
                ->rawColumns(['buyer_name', 'contact', 'actions'])
                ->make(true);
        }

        return view('quotations.index');
    }

    public function create()
    {
        // Only leads without quotations, newest first
        $leads = Lead::select('id', 'buyer_name')
            ->whereDoesntHave('quotations')
            ->orderByDesc('created_at') // latest leads first
            ->get();

        $productsArray = Product::all()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'weight' => $p->weight_kg,
            'price' => $p->quote_price,
        ])->toArray();

        $customersArray = Customer::select(
            'id',
            'name',
            'poc_name',
            'phone',
            'alternate_phone',
            'email',
            'address_line1',
            'address_line2',
            'district',
            'state',
            'pincode',
            'gst_number'
        )->get();

        return view('quotations.create', compact('leads', 'productsArray', 'customersArray'));
    }

    public function store(Request $request)
    {
        // Decode JSON from quote_edit_data
        if ($request->filled('quote_edit_data')) {
            $data = json_decode($request->quote_edit_data, true);
            if ($data === null) {
                return back()->with('error', 'Invalid quote_edit_data JSON');
            }

            $request->merge([
                'trucks' => $data['trucks'] ?? [],
                'prices' => $data['prices'] ?? [],
                'transport' => $data['transport'] ?? [],
                'subtotal' => $data['subtotal'] ?? 0,
                'gst_rate' => $data['gst_rate'] ?? 18,
                'total_amount' => $data['total_amount'] ?? 0,
                'net_total' => $data['net_total'] ?? 0,
                'distance_km' => $data['distance_km'] ?? 0,
                'remarks' => $data['remarks'] ?? '',
                'delivery_location_id' => $data['delivery_location_id'] ?? $request->delivery_location_id,
            ]);
        }

        // Auto-assign lead_id if missing
        if (!$request->filled('lead_id') && $request->filled('quotation_id')) {
            $existingQuotation = Quotation::find($request->quotation_id);
            if ($existingQuotation) {
                $request->merge(['lead_id' => $existingQuotation->lead_id]);
            }
        }

        // Validation
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'trucks' => 'required|array|min:1',
            'trucks.*.truck_id' => 'required|exists:tp_truck_types,id',
            'trucks.*.products' => 'required|array|min:1',
            'trucks.*.products.*.product_id' => 'required|exists:products,id',
            'trucks.*.products.*.qty' => 'required|numeric|min:0',
            'prices' => 'required|array|min:1',
            'prices.*.product_id' => 'required|exists:products,id',
            'prices.*.total_qty' => 'required|numeric|min:0',
            'prices.*.unit_price' => 'required|numeric|min:0',
            'prices.*.transport_unit' => 'nullable|numeric|min:0',
            'prices.*.total_unit_price' => 'nullable|numeric|min:0',
            'prices.*.total_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'net_total' => 'required|numeric|min:0',
        ]);

        $quotation = null;
        $version = null;
        DB::transaction(function () use ($request, &$quotation, &$version) {
            $userId = Auth::id();
            $leadId = $request->lead_id;

            // 🔹 Quotation: existing → new version OR brand new
            if ($request->filled('quotation_id')) {
                $quotation = Quotation::findOrFail($request->quotation_id);
                $lastVersion = QuoteVersion::where('quotation_id', $quotation->id)
                    ->orderByDesc('version_number')
                    ->first();
                $newVersionNumber = $lastVersion ? $lastVersion->version_number + 1 : 1;
                $quoteNumber = $quotation->quote_number;
            } else {
                $year = date('y');
                $monthLetter = chr(64 + date('n'));
                $latestQuotation = Quotation::where('quote_number', 'like', "Q{$year}{$monthLetter}%")
                    ->orderByDesc('id')
                    ->first();
                $nextNumber = $latestQuotation
                    ? str_pad((int)substr($latestQuotation->quote_number, -4) + 1, 4, '0', STR_PAD_LEFT)
                    : '0001';
                $quoteNumber = "Q{$year}{$monthLetter}{$nextNumber}";

                $quotation = Quotation::create([
                    'lead_id' => $leadId,
                    'quote_number' => $quoteNumber,
                    'status' => 'draft',
                    'total_amount' => $request->total_amount,
                    'created_by' => $userId,
                ]);

                $newVersionNumber = 1;
            }

            // Deactivate previous versions
            QuoteVersion::where('quotation_id', $quotation->id)->update(['is_active' => 0]);

            // Determine effective distance from truck-level entries (prefer edited values)
            $distanceFromTrucks = collect($request->trucks)
                ->pluck('distance_km')
                ->filter()
                ->first();

            $effectiveDistance = $distanceFromTrucks ?? $request->distance_km ?? 0;

            // 🔹 Create new version with customer & PDF fields
            $version = QuoteVersion::create([
                'quotation_id' => $quotation->id,
                'version_number' => $newVersionNumber,
                'subtotal' => $request->subtotal,
                'gst_rate' => $request->gst_rate ?? 18,
                'net_total' => $request->net_total,
                'distance_km' => $effectiveDistance,
                'delivery_location_id' => $request->delivery_location_id,
                'remarks' => $request->remarks ?? '',
                'is_active' => 1,
                'created_by' => $userId,

                // 🧾 Customer & PDF fields directly in version
                'customer_name' => $request->customer_name ?? $request->buyer_name ?? '',
                'customer_contact' => $request->customer_contact ?? $request->buyer_contact ?? '',
                'customer_address_line1' => $request->customer_address_line1 ?? '',
                'customer_address_line2' => $request->customer_address_line2 ?? '',
                'customer_district' => $request->customer_district ?? '',
                'customer_state' => $request->customer_state ?? '',
                'customer_pincode' => $request->customer_pincode ?? '',
                'customer_gst_number' => $request->customer_gst_number ?? '',
                'pdf_date' => $request->pdf_date ?? now()->format('Y-m-d'),
                'pdf_subject' => $request->pdf_subject ?? 'Quotation for supply of RCC Products',
                'pdf_terms' => $request->pdf_terms ?? 'The above price includes loading and transportation. Unloading is under client scope.',
                'pdf_delivery' => $request->pdf_delivery ?? 'Materials are readily available. We can supply your requirement within 2 days as per your delivery schedule after placing your order.',

            ]);
            // 🧩 Save Additional Fields
            if ($request->filled('additional_fields')) {
                foreach ($request->input('additional_fields') as $index => $field) {
                    if (!empty($field['heading']) && !empty($field['content'])) {
                        QuoteAdditionalField::create([
                            'quote_version_id' => $version->id, // or $version->quotation_id (same)
                            'heading'       => $field['heading'],
                            'content'       => $field['content'],
                            'sort_order'  => $index,
                        ]);
                    }
                }
            }

            // 🛻 Trucks + Products
            foreach ($request->trucks as $truckData) {
                $truck = QuoteTruck::create([
                    'quote_version_id' => $version->id,
                    'truck_type_id' => $truckData['truck_id'],
                    'body_type' => $truckData['body_type'] ?? 'Truck',
                    'truck_cost' => $truckData['truck_cost'] ?? 0,
                    'unloading_charges' => $truckData['unloading_charges'] ?? 0,
                    'distance_km' => $truckData['distance_km'] ?? null,
                    'multiplier' => $truckData['multiplier'] ?? 1,
                    'rate_per_km' => $truckData['rate_per_km'] ?? 0,
                    'fixed_rate' => $truckData['fixed_rate'] ?? 0,
                    'total_weight' => $truckData['total_weight'] ?? 0,
                ]);

                foreach ($truckData['products'] as $p) {
                    QuoteTruckProduct::create([
                        'quote_truck_id' => $truck->id,
                        'product_id' => $p['product_id'],
                        'allocated_qty' => $p['qty'],
                        'weight_per_unit' => $p['weight_per_unit'] ?? 0,
                        'max_allowed_qty' => $p['max_allowed_qty'] ?? 0,
                    ]);
                }
            }

            // 💰 Price details
            foreach ($request->prices as $p) {
                QuotePriceDetail::create([
                    'quote_version_id' => $version->id,
                    'product_id' => $p['product_id'],
                    'total_qty' => $p['total_qty'],
                    'unit_price' => $p['unit_price'],
                    'transport_unit' => $p['transport_unit'] ?? 0,
                    'total_unit_price' => $p['total_unit_price'] ?? 0,
                    'total_price' => $p['total_price'],
                ]);
            }

            // Mark this version active in quotation
            $quotation->update([
                'current_version' => $version->id,
                'total_amount' => $request->total_amount,
                'modified_by' => Auth::id(), // ✅ track who made the latest version
            ]);


            // ---------------- Update Distance Cache ----------------
            $toId = $request->delivery_location_id ?? null;
            $submittedDistance = $request->distance_km ?? null;
            $submittedDuration = $request->duration_minutes ?? null;

            if ($toId && $submittedDistance !== null && $submittedDuration !== null) {
                $cache = \App\Models\DistanceCache::firstOrNew([
                    'to_location_id' => $toId,
                ]);

                if (
                    $cache->distance_km !== (int) $submittedDistance ||
                    $cache->duration_minutes !== (int) $submittedDuration
                ) {
                    $cache->distance_km = (int) $submittedDistance;
                    $cache->duration_minutes = (int) $submittedDuration;
                    $cache->user_update = true;
                    $cache->last_updated = now();
                    $cache->save();
                }
            }
            // 🔄 Sync updated fields to Lead (with audit)
            $lead = \App\Models\Lead::find($request->lead_id);

            if ($lead) {
                $updates = [];

                // Compare and update delivery_location_id
                if (!empty($request->delivery_location_id) &&
                    $lead->delivery_location_id != $request->delivery_location_id) {
                    $updates['delivery_location_id'] = $request->delivery_location_id;
                }

                // Compare and update product_detail
                if (!empty($request->product_detail) &&
                    trim($lead->product_detail) !== trim($request->product_detail)) {
                    $updates['product_detail'] = $request->product_detail;
                }

                // Only save if something changed (this will trigger OwenIt audit)
                if (!empty($updates)) {
                    $lead->fill($updates);
                    $lead->save();

                }
            }
        });

        return redirect()
            ->route('quotations.index')
            ->with('download_pdf', route('quotations.download-version', [
                'quotation' => $quotation->id,
                'version'   => $version->id,
            ]))
            ->with('success', 'Quotation version created successfully. Downloading PDF...');
    }

    public function download($quotationId)
    {
        $quotation = Quotation::with(['lead', 'versions.trucks.products.product', 'customer', 'modifier'])->findOrFail($quotationId);
        $version = $quotation->versions->sortByDesc('version_number')->first();

        $data = [
            'quotation' => $quotation,
            'version'   => $version,
            'trucks'    => $version?->trucks ?? [],
        ];

        // 🟢 Dompdf setup for local images
        $pdf = Pdf::setOptions([
            'isRemoteEnabled' => false,
            'chroot' => public_path(),
        ])->loadView('quotations.pdf', $data)
          ->setPaper('A4', 'portrait');

        // Modifier (first 2 letters only)
        $modifiedBy = optional($quotation->modifier)->name ?? 'Unknown';
        $modifiedByShort = substr($modifiedBy, 0, 2);
        $modifiedBySlug = strtoupper(Str::slug($modifiedByShort, '_'));

        // Buyer (full name, uppercase)
        $buyerName = $version->customer_name ?? 'Buyer';
        $buyerNameSlug = strtoupper(Str::slug($buyerName, '_'));

        // 🔹 Build filename
        $filename = "{$quotation->quote_number}_{$modifiedBySlug}_{$buyerNameSlug}.pdf";
        return $pdf->download($filename);
    }

    public function downloadVersion($quotationId, $versionId)
    {
        $quotation = Quotation::with(['lead', 'versions.trucks.products.product', 'customer', 'modifier'])->findOrFail($quotationId);
        $version = $quotation->versions()->where('id', $versionId)->firstOrFail();

        if (!$version) {
            abort(404, 'Version not found for this quotation.');
        }

        $data = [
            'quotation' => $quotation,
            'version'   => $version,
            'trucks'    => $version->trucks ?? [],
        ];

        // 🟢 Dompdf setup for local images
        $pdf = Pdf::setOptions([
            'isRemoteEnabled' => false,
            'chroot' => public_path(),
        ])->loadView('quotations.pdf', $data)
          ->setPaper('A4', 'portrait');

       // Modifier (first 2 letters only)
       $modifiedBy = optional($quotation->modifier)->name ?? 'Unknown';
       $modifiedByShort = substr($modifiedBy, 0, 2);
       $modifiedBySlug = strtoupper(Str::slug($modifiedByShort, '_'));

       // Buyer (full name, uppercase)
       $buyerName = $version->customer_name ?? 'Buyer';
       $buyerNameSlug = strtoupper(Str::slug($buyerName, '_'));

        // 🔹 Build filename with version
        $filename = "{$quotation->quote_number}-V{$version->version_number}_{$modifiedBySlug}_{$buyerNameSlug}.pdf";
        return $pdf->download($filename);
    }

    public function previewPdf(Request $request)
    {
        try {
            // Decode the JS payload (quote data)
            $payload = [];
            if ($request->filled('quote_edit_data')) {
                $decoded = json_decode($request->quote_edit_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $payload = $decoded;
                }
            }

            // Mock quotation (not saved)
            $quotation = (object)[
                'quote_number' => 'PREVIEW',
                'modifier' => Auth::user(),
                'lead' => (object)[
                    'buyer_name' => $request->customer_name ?: 'Customer',
                    'buyer_contact' => $request->customer_contact,
                    'created_at' => now(),
                ],
            ];

            // Mock version (like Version model)
            $version = (object)[
                'version_number' => 0,
                'customer_name' => $request->customer_name,
                'customer_contact' => $request->customer_contact,
                'customer_address_line1' => $request->customer_address_line1,
                'customer_address_line2' => $request->customer_address_line2,
                'customer_district' => $request->customer_district,
                'customer_state' => $request->customer_state,
                'customer_pincode' => $request->customer_pincode,
                'customer_gst_number' => $request->customer_gst_number,
                'pdf_date' => $request->pdf_date ?? now()->format('Y-m-d'),
                'pdf_subject' => $request->pdf_subject ?? 'Quotation for supply of RCC Products',
                'pdf_terms' => $request->pdf_terms ?? 'The above price includes loading and transportation. Unloading is under client scope.',
                'pdf_delivery' => $request->pdf_delivery ?? 'Materials will be delivered as per your schedule.',
                'gst_rate' => 18,
            ];

            // 🔹 Convert price details (from JS)
            $version->priceDetails = collect($payload['prices'] ?? [])->map(function ($p) {
                return (object)[
                    'product' => (object)[
                        'name' => $p['product_name'] ?? 'Product',
                        'unit' => (object)['name' => $p['unit_name'] ?? 'Nos'],
                    ],
                    'unit_price' => $p['unit_price'] ?? 0,
                    'transport_unit' => $p['transport_unit'] ?? 0,
                    'total_unit_price' => $p['total_unit_price'] ?? 0,
                    'total_qty' => $p['total_qty'] ?? 1,
                    'total_price' => $p['total_price'] ?? 0,
                ];
            });

            // 🔹 Trucks
            $version->trucks = collect($payload['trucks'] ?? [])->map(fn($t) => (object)$t);

            // ✅ NEW: Add support for Additional Fields
            // These come from your form inputs, probably like additional_fields[0][heading], additional_fields[0][content]
            $additional = collect($request->input('additional_fields', []))
                ->filter(fn($f) => !empty($f['heading']) || !empty($f['content']))
                ->map(fn($f) => (object)[
                    'heading' => $f['heading'] ?? '',
                    'content' => $f['content'] ?? '',
                ]);

            $version->additionalFields = $additional;

            // Final data passed to view
            $data = [
                'quotation' => $quotation,
                'version' => $version,
                'trucks' => $version->trucks,
            ];

            // Generate PDF
            $pdf = \PDF::setOptions([
                'isRemoteEnabled' => false,
                'chroot' => public_path(),
            ])->loadView('quotations.pdf', $data)
              ->setPaper('A4', 'portrait');

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf');

        } catch (\Throwable $e) {
            \Log::error('❌ Preview PDF failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'PDF generation failed.'], 500);
        }
    }


    public function fetchQuotationData($id)
    {
        $quotation = Quotation::with([
            'lead.location.latestCache',
            'creator',
            'versions.trucks.products.product',
            'versions.priceDetails.product',
            'versions.additionalFields',
        ])->findOrFail($id);

        // ✅ Allow ?version_id=xx to fetch that specific version
        $versionId = request()->get('version_id');
        $version = $versionId
            ? $quotation->versions->firstWhere('id', $versionId)
            : $quotation->versions->sortByDesc('version_number')->first();
        if (!$version) {
            return response()->json(['error' => 'Version not found'], 404);
        }

        $lead = $quotation->lead;
        $locationId = $lead?->delivery_location_id;
        $distance_km = null;
        $duration_minutes = null;
        $pincode = null;
        $fullLocation = null;

        // ✅ Delivery location details
        if ($locationId && $lead->location) {
            $location = $lead->location;
            $pincode = $location->pincode;
            $fullLocation = $location->full_location . '-' . $pincode;

            if ($location->latestCache) {
                $distance_km = round($location->latestCache->distance_km);
                $duration_minutes = round($location->latestCache->duration_minutes);
            }
        }

        // ✅ Static arrays for UI
        $productsArray = Product::all()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'weight' => $p->weight_kg,
            'price' => $p->quote_price,
        ])->toArray();

        $customersArray = Customer::select(
            'id',
            'name',
            'poc_name',
            'phone',
            'alternate_phone',
            'email',
            'address_line1',
            'address_line2',
            'district',
            'state',
            'pincode',
            'gst_number'
        )->get();

        // ✅ Full quote structure (trucks, price, etc.)
        $versionData = $this->getDbVersionData($version->id)->getData(true);

        // Additional fields for this version
        $additionalFields = $version->additionalFields
            ->sortBy('sort_order')
            ->map(fn($f) => [
                'heading' => $f->heading,
                'content' => $f->content,
            ])->values()->toArray();

        // Merge additional fields into versionData
        $versionData['additionalFields'] = $additionalFields;

        // ✅ Merge versionData into main response
        return response()->json([
            // ----- Lead info -----
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

            // ----- Quotation + Version info -----
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quote_number,
            'version' => $version,
            'versionData' => $versionData,
            'created_by' => $quotation->creator?->name,
            'created_at' => $quotation->created_at?->format('d-M-Y H:i'),

            // ----- UI arrays -----
            'productsArray' => $productsArray,
            'customersArray' => $customersArray,

            // ----- Customer snapshot (from version) -----
            'customer' => [
                'id' => $version->customer_id,
                'name' => $version->customer_name,
                'contact' => $version->customer_contact,
                'address_line1' => $version->customer_address_line1,
                'address_line2' => $version->customer_address_line2,
                'district' => $version->customer_district,
                'state' => $version->customer_state,
                'pincode' => $version->customer_pincode,
                'gst_number' => $version->customer_gst_number,
            ],

            // ----- PDF info (future) -----
            'pdf_subject' => $version->pdf_subject ?? '',
            'pdf_terms' => $version->pdf_terms ?? '',
            'pdf_delivery' => $version->pdf_delivery ?? '',
        ]);
    }

    public function createVersionPage($id)
    {
        // Load arrays for the JS form (unchanged)
        $productsArray = Product::all()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'weight' => $p->weight_kg,
            'price' => $p->quote_price,
        ])->toArray();

        $customersArray = Customer::select(
            'id',
            'name',
            'poc_name',
            'phone',
            'alternate_phone',
            'email',
            'address_line1',
            'address_line2',
            'district',
            'state',
            'pincode',
            'gst_number'
        )->get();

        // ✅ Load quotation WITH versions
        $quotation = Quotation::with('versions')->findOrFail($id);

        // ✅ Decide which version to show
        $version = $quotation->versions
            ->firstWhere('id', request('version_id'))
            ?? $quotation->versions->sortByDesc('version_number')->first();

        return view('quotations.create_version', [
            'quotation' => $quotation,
            'version' => $version,                 // ✅ NEW
            'productsArray' => $productsArray,
            'customersArray' => $customersArray,
        ]);
    }

    public function editPage($id)
    {
        // just load necessary arrays for JS and the empty form
        $productsArray = Product::all()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'weight' => $p->weight_kg,
            'price' => $p->quote_price,
        ])->toArray();

        $customersArray = Customer::select(
            'id',
            'name',
            'poc_name',
            'phone',
            'alternate_phone',
            'email',
            'address_line1',
            'address_line2',
            'district',
            'state',
            'pincode',
            'gst_number'
        )->get();

        return view('quotations.create', compact('productsArray', 'customersArray'));
    }

    public function fetchVersion($versionId)
    {
        try {
            $version = QuoteVersion::with(['trucks.products', 'priceDetails.product'])->findOrFail($versionId);

            return response()->json([
                'version_number' => $version->version_number,
                'pincode' => $version->pincode,
                'delivery_location' => $version->delivery_location,
                'delivery_location_id' => $version->delivery_location_id,
                'distance_km' => $version->distance_km,
                'duration_minutes' => $version->duration_minutes,
                'product_detail' => $version->product_detail,
                'estimated_cost' => $version->net_total,
                'details_html' => $version->details_html ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ fetchVersion failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDbVersionData($versionId)
    {
        // ✅ Load the quotation + lead relationship too
        $version = QuoteVersion::with([
            'quotation.lead',
            'trucks.truckType',
            'trucks.products.product',
            'priceDetails.product',
            'additionalFields',
        ])->findOrFail($versionId);

        // 🟢 Access lead details through quotation
        $lead = $version->quotation?->lead;
        $locationId = $lead?->delivery_location_id;
        $districtId = $lead?->district_id; // optional, if your leads table has district_id

        // 🟢 Get district rates by location or district
        $districtRates = collect();

        if ($locationId) {
            $districtRates = TpDistrictRate::where('location_id', $locationId)
                ->select('truck_type_id', 'rate')
                ->get();
        }

        if ($districtRates->isEmpty() && $districtId) {
            $districtRates = TpDistrictRate::where('district_id', $districtId)
                ->select('truck_type_id', 'rate')
                ->get();
        }

        $response = [
            // 🟢 Product list for price table
            'available_products' => $version->priceDetails->map(fn($p) => [
                'id' => $p->product_id,
                'name' => $p->product->name ?? '-',
                'sku' => $p->product->sku ?? '',
                'requested_qty' => $p->total_qty,
                'price' => $p->unit_price,
                'transport_unit' => $p->transport_unit ?? 0,
                'total_unit_price' => $p->total_unit_price ?? 0,
                'total_price' => $p->total_price ?? 0,
                'weight_kg' => $p->product->weight_kg ?? 0,
            ])->values(),

            // 🟢 Draft allocations for truck-product matrix
            'draft_allocations' => $version->trucks->map(fn($t) => [
                'truck_id' => $t->truck_type_id,
                'truck_name' => $t->truckType->name ?? '',
                'body_type' => $t->body_type,
                'truck_cost' => $t->truck_cost,
                'unloading_charges' => $t->unloading_charges,
                'distance_km' => $t->distance_km,
                'items' => $t->products->map(function ($p) use ($version) {
                    $priceDetail = $version->priceDetails->firstWhere('product_id', $p->product_id);
                    return [
                        'product_id'      => $p->product_id,
                        'qty'             => $p->allocated_qty,
                        'requested_qty'   => $priceDetail?->total_qty ?? 0,
                        'max_allowed_qty' => $p->max_allowed_qty ?? 0,
                        'unit_price'      => $priceDetail?->unit_price ?? 0,
                        'transport_unit'  => $priceDetail?->transport_unit ?? 0,
                        'total_price'     => $priceDetail?->total_price ?? 0,
                    ];
                }),
            ])->values(),

            // 🟢 Transport summary
            'transport' => $version->trucks->map(fn($t) => [
                'truck_name'  => $t->truckType->name ?? '',
                'rate'        => $t->rate_per_km > 0 ? $t->rate_per_km : ($t->fixed_rate ?? 0),
                'rate_per_km' => $t->rate_per_km ?? 0,
                'fixed_rate'  => $t->fixed_rate ?? 0,
                'multiplier'  => $t->multiplier ?? 1,
                'distance'    => $t->distance_km ?? 0,
                'unloading'   => $t->unloading_charges ?? 0,
                'cost'        => $t->truck_cost ?? 0,
            ])->values(),

            // 🟢 Totals
            'subtotal' => $version->subtotal ?? 0,
            'gst_rate' => $version->gst_rate ?? 18,
            'net_total' => $version->net_total ?? 0,
            'distance_km' => $version->distance_km ?? 0,
            'remarks' => $version->remarks ?? '',
            'total_transport' => $version->trucks->sum('truck_cost'),
            'total_weight' => $version->trucks
                ->flatMap(fn($t) => $t->products)
                ->sum(fn($p) => ($p->product->weight_kg ?? 0) * $p->allocated_qty),

            // 🟢 Customer snapshot
            'customer' => [
                'id' => $version->customer_id,
                'name' => $version->customer_name,
                'contact' => $version->customer_contact,
                'address_line1' => $version->customer_address_line1,
                'address_line2' => $version->customer_address_line2,
                'district' => $version->customer_district,
                'state' => $version->customer_state,
                'pincode' => $version->customer_pincode,
                'gst_number' => $version->customer_gst_number,
            ],

            // 🟢 PDF snapshot
            'pdf' => [
                'pdf_date' => $version->pdf_date ?? now()->format('Y-m-d'),
                'pdf_subject' => $version->pdf_subject ?? 'Quotation for supply of RCC Products',
                'pdf_terms' => $version->pdf_terms ?? 'The above price includes loading and transportation. Unloading is under client scope.',
                'pdf_delivery' => $version->pdf_delivery ?? 'Materials are readily available. We can supply your requirement within 2 days as per your delivery schedule after placing your order.',
            ],

            // 🟢 Additional fields
            'additionalFields' => $version->additionalFields
                ->sortBy('sort_order') // optional, keep original order
                ->map(fn($f) => [
                    'heading' => $f->heading,
                    'content' => $f->content,
                ])->values()->toArray(),

            // 🟢 Static lists
            'available_trucks' => TruckType::select(
                'id', 'name', 'rate_per_km', 'unloading_charges_below_150', 'unloading_charges_above_150'
            )->get(),

            'truck_capacities' => TruckCapacity::select('truck_type_id', 'product_id', 'body_type', 'max_units')->get(),
            'district_rates' => $districtRates, // ✅ filtered by lead location
            'km_multipliers' => TpKmMultiplier::select('min_km', 'max_km', 'multiplier')->get(),
        ];
        return response()->json($response);
    }
}
