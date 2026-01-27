<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product\Parameter;

class ParameterUnitsSeeder extends Seeder
{
    public function run(): void
    {
        // Map of parameters → allowed units
        $unitsMap = [
            'Length'    => ['MM','CM','MTR'],
            'Diameter'  => ['MM','CM'],
            'Height'    => ['MM','IN','FT'],
            'Breadth'   => ['MM','IN','FT'],
            'Thickness' => ['MM','CM'],
        ];

        foreach ($unitsMap as $paramName => $units) {
            $param = Parameter::where('name', $paramName)->first();
            if (!$param) continue;

            foreach ($units as $unit) {
                DB::table('prod_parameter_units')->updateOrInsert(
                    [
                        'prod_parameter_id' => $param->id,
                        'unit' => $unit
                    ],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
