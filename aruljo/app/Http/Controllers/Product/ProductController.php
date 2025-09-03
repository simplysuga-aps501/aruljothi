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
        ]);
    }

    /**
     * Load parameters dynamically based on template
     */
public function getParameters($templateId)
{
    $configs = ParameterConfig::with([
        'parameter.options.dependencies.parameter.options',
        'parameter.options.dependencies.parameter.units',
        'parameter.units',
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
              'selling_price' => 'nullable|numeric|min:0',
              'weight_kg' => 'nullable|numeric|min:0',

              // Parameters
              'parameters' => 'nullable|array',
              'parameters.*.parameter_id' => 'required|exists:prod_parameters,id',
              'parameters.*.value' => 'required',
              'parameters.*.unit_id' => 'nullable|exists:prod_parameter_units,id',

              // Truck Capacities
              'truck_capacities' => 'nullable|array',
              'truck_capacities.*.with_body' => 'nullable|numeric|min:0',
              'truck_capacities.*.without_body' => 'nullable|numeric|min:0',
          ]);

          $product = DB::transaction(function () use ($validated, $request) {
              // ✅ Create product
              $product = Product::create([
                  'name' => strtoupper($validated['name']),
                  'sku' => 'PRD-' . strtoupper(uniqid()),
                  'description' => $request->input('description', ''),
                  'prod_template_id' => $validated['prod_template_id'] ?? null,
                  'unit_id' => $validated['unit_id'] ?? null,
                  'hsncode_id' => $validated['hsncode_id'] ?? null,
                  'stock_count' => 0,
                  'selling_price' => $validated['selling_price'] ?? 0,
                  'weight_kg' => $validated['weight_kg'] ?? 0,
                  'modified_by' => auth()->id(),
              ]);

              // ✅ Store parameter values
              if (!empty($validated['parameters'])) {
                  foreach ($validated['parameters'] as $param) {
                      $product->parameterValues()->create([
                          'prod_parameter_id' => $param['parameter_id'],
                          'value' => $param['value'],
                          'unit_id' => $param['unit_id'] ?? null,
                          'modified_by' => auth()->id(),
                      ]);
                  }
              }

              // ✅ Store truck capacities (with & without body)
              if (!empty($validated['truck_capacities'])) {
                  foreach ($validated['truck_capacities'] as $truckId => $capacity) {

                      // Save WITH body only if > 0
                      if (!empty($capacity['with_body'])) {
                          $product->truckCapacities()->updateOrCreate(
                              [
                                  'truck_type_id' => $truckId,
                                  'body_type' => 'with_body',
                              ],
                              [
                                  'max_units' => $capacity['with_body'],
                              ]
                          );
                      }

                      // Save WITHOUT body only if > 0
                      if (!empty($capacity['without_body'])) {
                          $product->truckCapacities()->updateOrCreate(
                              [
                                  'truck_type_id' => $truckId,
                                  'body_type' => 'without_body',
                              ],
                              [
                                  'max_units' => $capacity['without_body'],
                              ]
                          );
                      }
                  }
              }
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
          \Log::error('Product Create Error: ' . $e->getMessage(), [
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
               'edit_selling_price' => 'nullable|numeric|min:0',
               'edit_weight_kg' => 'nullable|numeric|min:0',

               'edit_truck_capacities' => 'nullable|array',
               'edit_truck_capacities.*.with_body' => 'nullable|numeric|min:0',
               'edit_truck_capacities.*.without_body' => 'nullable|numeric|min:0',
           ]);

           $product = DB::transaction(function () use ($validated, $product) {


               $product->update([
                   'selling_price' => $validated['edit_selling_price'] ?? $product->selling_price,
                   'weight_kg' => $validated['edit_weight_kg'] ?? $product->weight_kg,
                   'modified_by' => auth()->id(),
               ]);

               // Update truck capacities
               if (!empty($validated['edit_truck_capacities'])) {
                   foreach ($validated['edit_truck_capacities'] as $truckId => $capacity) {

                       // WITH body
                       if (isset($capacity['with_body'])) {
                           $product->truckCapacities()->updateOrCreate(
                               [
                                   'truck_type_id' => $truckId,
                                   'body_type' => 'with_body',
                               ],
                               [
                                   'max_units' => $capacity['with_body'],
                               ]
                           );
                       }

                       // WITHOUT body
                       if (isset($capacity['without_body'])) {
                           $product->truckCapacities()->updateOrCreate(
                               [
                                   'truck_type_id' => $truckId,
                                   'body_type' => 'without_body',
                               ],
                               [
                                   'max_units' => $capacity['without_body'],
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
           \Log::error('Product Update Error: ' . $e->getMessage(), [
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
