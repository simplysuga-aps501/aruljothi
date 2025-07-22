<?php

namespace App\Http\Controllers;

use App\Models\Product\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function store(Request $request)
    {
        Unit::create($request->only('name'));
        return back();
    }
}
