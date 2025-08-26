<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Hsncode;
use Illuminate\Http\Request;

class HsncodeController extends Controller
{
    // Store a new HSN code
    public function store(Request $request)
    {
        $request->validate(
            [
                'name'        => 'required|string|max:255|unique:hsncodes,name',
                'description' => 'nullable|string|max:1000',
            ],
            ['name.unique' => 'This HSN code already exists.']
        );

        $hsn = Hsncode::create([
            'name'        => $request->name, // mutator uppercases
            'description' => $request->description,
            'modified_by' => auth()->id(),
        ]);

        return response()->json($hsn, 201);
    }

    // Delete an HSN code if not attached to any product
    public function destroy(Hsncode $hsncode)
    {
        if ($hsncode->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete: HSN code attached to products.'
            ], 400);
        }

        $hsncode->delete();

        return response()->json([
            'message' => 'HSN code deleted successfully.'
        ]);
    }
}
