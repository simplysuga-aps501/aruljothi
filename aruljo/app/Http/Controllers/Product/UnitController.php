<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{


     public function store(Request $request)
     {
        $request->validate(
            ['name' => 'required|string|max:255|unique:units,name'],
            ['name.unique' => 'This unit already exists.']
        );

        $unit = Unit::create([
            'name'        => $request->name, // mutator uppercases
            'modified_by' => auth()->id(),
        ]);

        return response()->json($unit, 201);
     }

    public function destroy($id)
    {
        $unit = Unit::withCount('products')->findOrFail($id);

        if ($unit->products_count > 0) {
            return response()->json(['error' => 'Unit is attached to products'], 422);
        }

        $unit->delete();
        return response()->json(['success' => true]);
    }
}
