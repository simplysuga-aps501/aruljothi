<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameterUnit;

class ProductParameterUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
        {
            $units = [
                'mm',
                'in',
                'ft',
                'mtr',
                'square',
                'round',
                'kv',
                'liters',
            ];

            foreach ($units as $unit) {
                ProductParameterUnit::create([
                    'name' => $unit,
                    'modified_by' => null,
                ]);
            }
        }
}
