<?php

namespace App\Http\Controllers;

use App\Models\Product\Hsncode;
use Illuminate\Http\Request;

class HsncodeController extends Controller
{
    public function store(Request $request)
    {
        Hsncode::create($request->only('name', 'description'));
        return back();
    }
}
