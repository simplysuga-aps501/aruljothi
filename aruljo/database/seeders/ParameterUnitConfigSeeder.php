<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ParameterUnitConfig;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterUnit;
use Carbon\Carbon;

class ParameterUnitConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Define parameter -> unit relationships by name
        $data = [
            'Diameter' => ['mm', 'in', 'ft', 'mtr'],
            'Length' => ['mm', 'in', 'ft', 'mtr'],
            'Height' => ['mm', 'in', 'ft', 'mtr'],
            'Width' => ['mm', 'in', 'ft', 'mtr'],
            'Thickness' => ['mm', 'in', 'ft', 'mtr'],
            'KV' => ['kv'],
            'Capacity' => ['liters'],
        ];

        foreach ($data as $paramName => $unitNames) {
            // Get parameter ID by name
            $parameter = Parameter::where('name', $paramName)->first();
            if (!$parameter) {
                $this->command->error("Parameter '{$paramName}' not found!");
                continue;
            }

            foreach ($unitNames as $unitName) {
                // Get unit ID by name
                $unit = ParameterUnit::where('name', $unitName)->first();
                if (!$unit) {
                    $this->command->error("Unit '{$unitName}' not found!");
                    continue;
                }

                // Create config
                ParameterUnitConfig::create([
                    'prod_parameter_id' => $parameter->id,
                    'prod_parameter_unit_id' => $unit->id,
                    'modified_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command->info('ParameterUnitConfigSeeder completed!');
    }
}
