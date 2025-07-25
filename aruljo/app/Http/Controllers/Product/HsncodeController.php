<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Hsncode;
use Illuminate\Http\Request;

class HsncodeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $hsncode = Hsncode::create([
            'name' => $request->name,
            'description' => $request->description,
            'modified_by' => auth()->id(),
        ]);

        return response()->json($hsncode);

    }

}
