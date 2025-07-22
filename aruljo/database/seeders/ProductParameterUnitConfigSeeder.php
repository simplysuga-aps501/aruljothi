<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameterUnitConfig;
use Carbon\Carbon;

class ProductParameterUnitConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['parameter_id' => 1, 'unit_id' => 1], // Diameter -> mm
            ['parameter_id' => 2, 'unit_id' => 4], // Length -> mtr
            ['parameter_id' => 3, 'unit_id' => 3], // Height -> ft
            ['parameter_id' => 4, 'unit_id' => 1], // Thickness -> mm
            ['parameter_id' => 5, 'unit_id' => 3], // Width -> ft
            ['parameter_id' => 6, 'unit_id' => 7], // KV -> kv
            ['parameter_id' => 7, 'unit_id' => 8], // Capacity -> liters
            ['parameter_id' => 8, 'unit_id' => 6], // Shape -> round
        ];

        foreach ($data as $entry) {
            ProductParameterUnitConfig::create([
                'product_parameter_id' => $entry['parameter_id'],
                'product_parameter_unit_id' => $entry['unit_id'],
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
