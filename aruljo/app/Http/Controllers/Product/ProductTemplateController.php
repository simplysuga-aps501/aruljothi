<?php

namespace App\Http\Controllers;

use App\Models\Product\ProductTemplate;
use Illuminate\Http\Request;

class ProductTemplateController extends Controller
{
    public function parameters($id)
    {
        $template = ProductTemplate::with(['productParameterConfigs.productParameter.parameterUnit'])->findOrFail($id);

        $parameters = $template->productParameterConfigs->map(function ($config) {
            $param = $config->productParameter;
            return [
                'id' => $param->id,
                'name' => $param->name,
                'unit_name' => optional($param->parameterUnit)->name,
                'options' => explode(',', $param->options ?? ''), // You should have a way to store options
            ];
        });

        return response()->json(['parameters' => $parameters]);
    }
}

