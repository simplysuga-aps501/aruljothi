<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function store(Request $request)
        {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $unit = Unit::create([
                'name' => $request->name,
                'modified_by' => auth()->id(),
            ]);

            return response()->json($unit);
        }
}
