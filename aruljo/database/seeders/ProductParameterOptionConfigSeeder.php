<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameterOptionConfig;
use Carbon\Carbon;

class ProductParameterOptionConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::parse('2025-07-22 10:00:00');

        $options = [
            ['product_parameter_id' => 8, 'parameter_option' => 'Round'],
            ['product_parameter_id' => 8, 'parameter_option' => 'Square'],
            ['product_parameter_id' => 9, 'parameter_option' => 'With Lid'],
            ['product_parameter_id' => 9, 'parameter_option' => 'Without Lid'],
            ['product_parameter_id' => 10, 'parameter_option' => 'NP2'],
            ['product_parameter_id' => 10, 'parameter_option' => 'NP3'],
            ['product_parameter_id' => 10, 'parameter_option' => 'NP4'],
            ['product_parameter_id' => 11, 'parameter_option' => 'Plain'],
            ['product_parameter_id' => 11, 'parameter_option' => 'Spygot'],
            ['product_parameter_id' => 11, 'parameter_option' => 'Male Female'],
            ['product_parameter_id' => 11, 'parameter_option' => 'Plain End With Separate Collar'],
        ];

        foreach ($options as $entry) {
            ProductParameterOptionConfig::create([
                'product_parameter_id' => $entry['product_parameter_id'],
                'parameter_option'     => $entry['parameter_option'],
                'modified_by'          => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
    }
}
