<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductTemplate;
use App\Models\Product\Unit;
use App\Models\Product\Hsncode;
use App\Models\Product\ProductParameterConfig;
use App\Models\Product\ProductParameterValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Show product index page
     */
    public function index()
    {
     return view('products.index', [
            'products' => Product::with(['unit', 'hsncode'])->get(),
            'product_templates' => ProductTemplate::all(),
            'units' => Unit::all(),
            'hsncodes' => Hsncode::all(),
        ]);
    }

    /**
     * Load parameters dynamically based on template
     */
    public function getParameters($templateId)
    {
        $configs = ProductParameterConfig::with([
            'productParameter.options',
            'productParameter.units',
        ])->where('product_template_id', $templateId)->get();

        $parameters = $configs->map(function ($config) {
            $param = $config->productParameter;

            return [
                'id' => $param->id,
                'name' => $param->name,
                'input_type' => $param->input_type,
                'description' => $param->description,
                'options' => $param->options->pluck('parameter_option')->toArray(),
                'units' => $param->units->pluck('name')->toArray(),
            ];
        });

        return response()->json(['parameters' => $parameters]);
    }

    /**
     * Store a new product with its parameter values
     */
   public function store(Request $request)
   {
       try {
           $validated = $request->validate([
               'name' => 'required|string|max:255',
               'product_template_id' => 'required|exists:product_templates,id',
               'unit_id' => 'required|exists:units,id',
               'hsncode_id' => 'required|exists:hsncodes,id',
               'parameters' => 'nullable|array',
               'parameters.*.parameter_id' => 'required|exists:product_parameters,id',
               'parameters.*.value' => 'required',
               'parameters.*.unit_id' => 'nullable|exists:parameter_units,id',
           ]);

           //Duplicate product
           $name = strtoupper($validated['name']);

           if (Product::where('name', $name)->exists()) {
               return response()->json([
                   'success' => false,
                   'message' => 'Product with this name already exists.',
               ], 422);
           }

           // ✅ Create product
           $product = Product::create([
               'name' => strtoupper($validated['name']),
               'sku' => 'PRD-' . strtoupper(uniqid()), // or use your SKU logic
               'description' => '', // Optional - build from params if needed
               'stock_count' => 0, // Default for now
               'product_template_id' => $validated['product_template_id'],
               'unit_id' => $validated['unit_id'],
               'hsncode_id' => $validated['hsncode_id'],
               'modified_by' => auth()->id(),
           ]);

           // ✅ Store parameter values
           if (!empty($validated['parameters'])) {
               foreach ($validated['parameters'] as $param) {
                   $product->parameterValues()->create([
                       'product_parameter_id' => $param['parameter_id'],
                       'value' => $param['value'],
                       'parameter_unit_id' => $param['unit_id'] ?? null,
                       'modified_by' => auth()->id(),
                   ]);
               }
           }

           return response()->json([
               'success' => true,
               'message' => 'Product created successfully.',
               'product_id' => $product->id,
           ]);
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

   public function destroy($id)
   {
       Product::findOrFail($id)->delete();
       return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
   }



}
