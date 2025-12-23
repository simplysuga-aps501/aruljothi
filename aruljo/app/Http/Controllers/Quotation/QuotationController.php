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

use App\Models\Product\Product;
use App\Models\Lead;
use App\Models\Transport\TruckType;
use App\Models\Transport\TpDistrictRate;
use App\Models\Transport\TpOffice;
use App\Models\Transport\TruckCapacity;
use App\Models\Transport\TpKmMultiplier;
use App\Models\Customer;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with(['lead', 'creator', 'versions', 'activeVersion'])
                ->whereHas('lead', function ($q) {
                    $q->where('status', '!=', 'Cancelled');
                })
                ->latest()
                ->get();

        return view('quotations.index', compact('quotations'));
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
        Log::info('Quotation Store Request:', $request->all());

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

        DB::transaction(function () use ($request) {
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
            $quotation->update(['current_version' => $version->id]);

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

                    \Log::info('Lead updated from quotation sync', [
                        'lead_id' => $lead->id,
                        'updated_fields' => $updates,
                        'source' => 'Quotation Store'
                    ]);
                }
            }
        });

        return redirect()
           ->route('quotations.index')
           ->with('success', 'Quotation version created successfully.');
    }


    public function download($quotationId)
    {
        $quotation = Quotation::with(['lead', 'versions.trucks.products.product'])->findOrFail($quotationId);
        $version = $quotation->versions->sortByDesc('version_number')->first();

        $data = [
            'quotation' => $quotation,
            'version' => $version,
            'trucks' => $version?->trucks ?? [],
        ];

        $pdf = PDF::loadView('quotations.pdf', $data)->setPaper('A4', 'portrait');

        return $pdf->download($quotation->quote_number . '.pdf');
    }

    // QuotationController.php
    public function downloadVersion($quotationId, $versionId)
    {
        // Load quotation with versions
        $quotation = Quotation::with(['lead', 'versions.trucks.products.product'])->findOrFail($quotationId);

        // Get the requested version
        $version = $quotation->versions->firstWhere('id', $versionId);

        if (!$version) {
            abort(404, 'Version not found for this quotation.');
        }

        $data = [
            'quotation' => $quotation,
            'version' => $version,
            'trucks' => $version->trucks ?? [],
        ];

        $pdf = PDF::loadView('quotations.pdf', $data)->setPaper('A4', 'portrait');

        return $pdf->download($quotation->quote_number . '-v' . $version->version_number . '.pdf');
    }


    public function fetchQuotationData($id)
    {
        $quotation = Quotation::with([
            'lead.location.latestCache',
            'creator',
            'versions.trucks.products.product',
            'versions.priceDetails.product',
        ])->findOrFail($id);

        // ✅ Allow ?version_id=xx to fetch that specific version
        $versionId = request()->get('version_id');
        $version = $versionId
            ? $quotation->versions->firstWhere('id', $versionId)
            : $quotation->versions->sortByDesc('version_number')->first();
        Log::info($version);
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
        $version = QuoteVersion::with(['trucks.truckType', 'trucks.products.product', 'priceDetails.product'])
            ->findOrFail($versionId);

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
                        'max_allowed_qty' => $p->max_allowed_qty ?? 0, // ✅ pull directly from DB column
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
                'rate_per_km' => $t->rate_per_km ?? 0,   // optional, for clarity
                'fixed_rate'  => $t->fixed_rate ?? 0,    // optional, for clarity
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

            // Static lists
            'available_trucks' => TruckType::select(
                'id', 'name', 'rate_per_km', 'unloading_charges_below_150', 'unloading_charges_above_150'
            )->get(),

            'truck_capacities' => TruckCapacity::select('truck_type_id', 'product_id', 'body_type', 'max_units')->get(),
            'district_rates' => TpDistrictRate::select('truck_type_id', 'rate')->get(),
            'km_multipliers' => TpKmMultiplier::select('min_km', 'max_km', 'multiplier')->get(),
        ];

        Log::info("Quote version response summary", [
            'version_id' => $versionId,
            'customer' => $response['customer'] ?? null,
            'counts' => [
                'trucks' => count($response['draft_allocations']),
                'products' => count($response['available_products']),
                'truck_types' => count($response['available_trucks']),
            ],
        ]);

        return response()->json($response);
    }


}
