<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductTemplate;
use App\Models\Product\Unit;
use App\Models\Product\Hsncode;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index', [
            'products' => Product::with(['unit', 'hsncode'])->get(),
            'product_templates' => ProductTemplate::all(),
            'units' => Unit::all(),
            'hsncodes' => Hsncode::all(),
        ]);
    }

    public function store(Request $request)
    {
        Product::create($request->all());
        return redirect()->route('products.index');
    }
}
