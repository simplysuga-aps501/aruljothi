<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Quotation\Quotation;
use App\Models\Quotation\QuoteVersion;
use App\Models\Quotation\QuoteTruck;
use App\Models\Quotation\QuoteProduct;
use App\Models\Product\Product;
use Illuminate\Support\Facades\Log;

class QuotationController extends Controller
{
    /**
     * Store a new quotation with its version, trucks, and products.
     */
    public function index()
    {
        $quotations = Quotation::with(['lead', 'createdBy'])->latest()->get();
        $leads = Lead::select('id', 'buyer_name')->orderBy('buyer_name')->get();

        return view('quotations.index', compact('quotations', 'leads'));
    }

    public function create()
    {
        $leads = \App\Models\Lead::select('id', 'buyer_name')->orderBy('buyer_name')->get();
        // Fetch all products for autocomplete
        $products = Product::all();
        $productsArray = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'weight' => $p->weight_kg,
            'price' => $p->quote_price,
        ])->toArray();
        return view('quotations.create', compact('leads', 'productsArray'));
    }
    public function store(Request $request)
    {
        Log::info('Quotation Store Request:', $request->all());
        // Decode JSON if needed
        if ($request->has('quote_edit_data')) {
            $data = json_decode($request->quote_edit_data, true);
            if ($data === null) {
                return response()->json(['error' => 'Invalid quote_edit_data JSON'], 400);
            }
            $request->merge([
                'trucks' => $data['trucks'] ?? [],
                'total_amount' => $data['total_amount'] ?? 0,
                'remarks' => $data['remarks'] ?? '',
            ]);
        }
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'total_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'trucks' => 'required|array|min:1',
            'trucks.*.truck_type_id' => 'required|exists:tp_truck_types,id',
            'trucks.*.products' => 'required|array|min:1',
            'trucks.*.products.*.product_id' => 'required|exists:products,id',
            'trucks.*.products.*.quantity' => 'required|numeric|min:0',
            'trucks.*.products.*.unit_price' => 'required|numeric|min:0',
            'trucks.*.products.*.total_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            $userId = Auth::id();
            $leadId = $request->lead_id;
            $year = date('y');

            // 🔹 Generate quote number: QYY-LeadId-Sequence
            $lastQuote = Quotation::where('lead_id', $leadId)
                ->where('quote_number', 'like', "Q{$year}-{$leadId}%")
                ->orderBy('quote_number', 'desc')
                ->first();

            $sequence = $lastQuote ? intval(substr($lastQuote->quote_number, -1)) + 1 : 1;
            $quoteNumber = "Q{$year}-{$leadId}-{$sequence}";

            // 1️⃣ Create main quotation
            $quotation = Quotation::create([
                'lead_id' => $leadId,
                'quote_number' => $quoteNumber,
                'status' => 'draft',
                'total_amount' => $request->total_amount,
                'created_by' => $userId,
            ]);

            // 2️⃣ Create first version
            $version = QuoteVersion::create([
                'quotation_id' => $quotation->id,
                'version_number' => 1,
                'total_amount' => $request->total_amount,
                'remarks' => $request->remarks,
                'created_by' => $userId,
            ]);

            // 3️⃣ Create trucks + products
            foreach ($request->trucks as $truckData) {
                $truck = QuoteTruck::create([
                    'quote_version_id' => $version->id,
                    'truck_type_id' => $truckData['truck_type_id'],
                    'body_type' => $truckData['body_type'] ?? 'Truck',
                    'truck_count' => $truckData['truck_count'] ?? 1,
                    'truck_cost' => $truckData['truck_cost'] ?? 0,
                    'distance_km' => $truckData['distance_km'] ?? null,
                    'multiplier' => $truckData['multiplier'] ?? 1,
                ]);

                foreach ($truckData['products'] as $p) {
                    QuoteProduct::create([
                        'quote_truck_id' => $truck->id,
                        'product_id' => $p['product_id'],
                        'quantity' => $p['quantity'],
                        'unit_price' => $p['unit_price'],
                        'total_price' => $p['total_price'],
                    ]);
                }
            }

            // 4️⃣ Set current version
            $quotation->update(['current_version' => $version->id]);

            Log::info("Quotation created with quote_number: {$quoteNumber}");
        });

        return response()->json(['success' => true, 'message' => 'Quotation saved successfully']);
    }
}
