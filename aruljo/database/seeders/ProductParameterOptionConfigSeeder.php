<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameter;
use App\Models\Product\ProductParameterOptionConfig;
use Carbon\Carbon;

class ProductParameterOptionConfigSeeder extends Seeder
{
    public function run(): void
    {
        $parameterMap = ProductParameter::pluck('id', 'name')->toArray();
        $now = Carbon::parse('2025-07-22 10:00:00');

        $options = [
            ['param' => 'Class', 'option' => 'NP2'],
            ['param' => 'Class', 'option' => 'NP3'],
            ['param' => 'Class', 'option' => 'NP4'],
            ['param' => 'Type', 'option' => 'Plain'],
            ['param' => 'Type', 'option' => 'Spygot'],
            ['param' => 'Type', 'option' => 'Male Female'],
            ['param' => 'Type', 'option' => 'Plain End With Separate Collar'],
            ['param' => 'Shape', 'option' => 'Round'],
            ['param' => 'Shape', 'option' => 'Square'],
            ['param' => 'Lid', 'option' => 'With Lid'],
            ['param' => 'Lid', 'option' => 'Without Lid'],
        ];

        foreach ($options as $entry) {
            if (isset($parameterMap[$entry['param']])) {
                ProductParameterOptionConfig::create([
                    'product_parameter_id' => $parameterMap[$entry['param']],
                    'parameter_option'     => $entry['option'],
                    'modified_by'          => null, // or use a user_id if you have users
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }
        }
    }
}
