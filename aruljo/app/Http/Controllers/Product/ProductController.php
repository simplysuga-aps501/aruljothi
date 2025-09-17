<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\Template;
use App\Models\Product\Unit;
use App\Models\Product\Hsncode;
use App\Models\Product\ParameterConfig;
use App\Models\Product\ParameterValue;
use App\Models\Transport\TruckType;
use App\Models\Transport\TruckCapacity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Show product index page
     */
    public function index()
    {
        return view('products.index', [
            'products' => Product::with(['unit', 'hsncode'])->get(),
            'product_templates' => Template::all(),
            'units' => Unit::all(),
            'hsncodes' => Hsncode::all(),
            'truck_types' => TruckType::all(),
            'body_types' =>TruckCapacity::getBodyTypes(),
        ]);
    }

    /**
     * Load parameters dynamically based on template
     */
    public function getParameters($templateId)
    {
        $configs = ParameterConfig::with([
            'parameter.options.dependencies.parameter.options',
            // removed parameter.units relation (no separate table now)
        ])
        ->where('prod_template_id', $templateId)
        ->get();

        return response()->json([
            'configs' => $configs,
        ]);
    }

    /**
     * Store a new product with its parameter values
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:products,name',
                'prod_template_id' => 'nullable|exists:prod_templates,id',
                'unit_id' => 'nullable|exists:units,id',
                'hsncode_id' => 'nullable|exists:hsncodes,id',
                'quote_price' => 'nullable|numeric|min:0',
                'weight_kg' => 'nullable|numeric|min:0',
                'parameters' => 'nullable|array',
                'parameters.*.parameter_id' => 'required|exists:prod_parameters,id',
                'parameters.*.value' => 'required',
                'truck_capacities' => 'nullable|array',
                'truck_capacities.*.truck' => 'nullable|numeric|min:0',
                'truck_capacities.*.open_body_truck' => 'nullable|numeric|min:0',
            ]);

            Log::debug('✅ Validated product data', ['validated' => $validated]);

            $product = DB::transaction(function () use ($validated, $request) {
                // Create product
                $product = Product::create([
                    'name'            => strtoupper($validated['name']),
                    'description'     => $request->input('description', ''),
                    'prod_template_id'=> $validated['prod_template_id'] ?? null,
                    'unit_id'         => $validated['unit_id'] ?? null,
                    'hsncode_id'      => $validated['hsncode_id'] ?? null,
                    'stock_count'     => 0,
                    'quote_price'     => $validated['quote_price'] ?? 0,
                    'weight_kg'       => $validated['weight_kg'] ?? 0,
                    'modified_by'     => auth()->id(),
                ]);

                Log::debug('✅ Created Product', ['product_id' => $product->id, 'name' => $product->name]);

                // Save parameter values
                $savedParams = [];
                if (!empty($validated['parameters'])) {
                    foreach ($validated['parameters'] as $param) {
                        $pv = $product->parameterValues()->create([
                            'prod_parameter_id' => $param['parameter_id'],
                            'value'             => $param['value'],
                            'modified_by'       => auth()->id(),
                        ]);
                        $savedParams[] = $pv;
                        Log::debug('📝 Parameter saved', [
                            'parameter_id' => $param['parameter_id'],
                            'value' => $param['value'],
                            'pv_id' => $pv->id
                        ]);
                    }
                }

                // Save truck capacities
                if (!empty($validated['truck_capacities'])) {
                    foreach ($validated['truck_capacities'] as $truckId => $capacity) {
                        if (!empty($capacity['truck'])) {
                            $tc = $product->truckCapacities()->updateOrCreate(
                                ['truck_type_id' => $truckId, 'body_type' => 'truck'],
                                ['max_units' => $capacity['truck']]
                            );
                            Log::debug('🚚 Truck capacity saved (with body)', ['truck_id' => $truckId, 'capacity' => $capacity['truck'], 'tc_id' => $tc->id]);
                        }
                        if (!empty($capacity['open_body_truck'])) {
                            $tc = $product->truckCapacities()->updateOrCreate(
                                ['truck_type_id' => $truckId, 'body_type' => 'open_body_truck'],
                                ['max_units' => $capacity['open_body_truck']]
                            );
                            Log::debug('🚚 Truck capacity saved (without body)', ['truck_id' => $truckId, 'capacity' => $capacity['open_body_truck'], 'tc_id' => $tc->id]);
                        }
                    }
                }

                // Build SKU
                $skuParts = [];
                if ($product->template && $product->template->abbreviation) {
                    $skuParts[] = strtoupper($product->template->abbreviation);
                }

                $paramModels = \App\Models\Product\Parameter::with('options')
                    ->whereIn('id', collect($validated['parameters'] ?? [])->pluck('parameter_id'))
                    ->get();

                $seenParams = [];
                foreach ($validated['parameters'] ?? [] as $param) {
                    if (in_array($param['parameter_id'], $seenParams)) continue;
                    $seenParams[] = $param['parameter_id'];

                    $pModel = $paramModels->firstWhere('id', $param['parameter_id']);
                    if (!$pModel) {
                        Log::debug('❌ Parameter model not found', ['param' => $param]);
                        continue;
                    }

                    Log::debug('🔍 Processing parameter', [
                        'id' => $pModel->id,
                        'name' => $pModel->name,
                        'type' => $pModel->input_type,
                        'value' => $param['value']
                    ]);

                    if ($pModel->input_type === 'number') {
                        $abbr = $pModel->abbreviation ?: '';
                        $part = strtoupper($param['value'] . $abbr);
                        Log::debug('➡️ Number SKU part', ['part' => $part]);
                        $skuParts[] = $part;
                        Log::debug('Inside number');

                    } elseif ($pModel->input_type === 'select') {
                          $opt  = $pModel->options->firstWhere('parameter_option', $param['value']);
                          $abbr = $opt && $opt->abbreviation ? $opt->abbreviation : null;

                          // Skip if abbreviation is null or empty
                          if (!$abbr) {
                              Log::debug('Skipping SKU part because abbreviation is null', ['option' => $param['value']]);
                              continue;
                          }

                          $part = strtoupper($abbr);
                          $skuParts[] = $part;

                          Log::debug('➡️ Select SKU part', [
                              'option' => $param['value'],
                              'abbr'   => $abbr,
                              'part'   => $part
                          ]);
                          Log::debug('Inside select');
                      }

                }

                $sku = implode('-', $skuParts);
                $product->update(['sku' => $sku]);
                Log::debug('🏷 Final SKU', ['sku' => $sku, 'product_id' => $product->id]);

                return $product;
            });

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'product_id' => $product->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Product Create Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating product: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function edit(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'edit_quote_price' => 'nullable|numeric|min:0',
                'edit_weight_kg' => 'nullable|numeric|min:0',

                'edit_truck_capacities' => 'nullable|array',
                'edit_truck_capacities.*.truck' => 'nullable|numeric|min:0',
                'edit_truck_capacities.*.open_body_truck' => 'nullable|numeric|min:0',
            ]);

            $product = DB::transaction(function () use ($validated, $product) {
                $product->update([
                    'quote_price' => $validated['edit_quote_price'] ?? $product->quote_price,
                    'weight_kg' => $validated['edit_weight_kg'] ?? $product->weight_kg,
                    'modified_by' => auth()->id(),
                ]);

                if (!empty($validated['edit_truck_capacities'])) {
                    foreach ($validated['edit_truck_capacities'] as $truckId => $capacity) {
                        if (isset($capacity['truck'])) {
                            $product->truckCapacities()->updateOrCreate(
                                [
                                    'truck_type_id' => $truckId,
                                    'body_type' => 'truck',
                                ],
                                [
                                    'max_units' => $capacity['truck'],
                                ]
                            );
                        }

                        if (isset($capacity['open_body_truck'])) {
                            $product->truckCapacities()->updateOrCreate(
                                [
                                    'truck_type_id' => $truckId,
                                    'body_type' => 'open_body_truck',
                                ],
                                [
                                    'max_units' => $capacity['open_body_truck'],
                                ]
                            );
                        }
                    }
                }

                return $product;
            });

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'product_id' => $product->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Product Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
