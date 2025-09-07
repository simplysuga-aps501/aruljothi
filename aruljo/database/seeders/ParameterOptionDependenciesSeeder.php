<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ParameterOptionConfig;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterOptionDependency;

class ParameterOptionDependenciesSeeder extends Seeder
{
    public function run(): void
    {
        // Build lookup maps
        $optionIdMap = ParameterOptionConfig::pluck('id', 'parameter_option');
        // ['Round' => 1, 'Square' => 2, ...]

        $paramIdMap  = Parameter::pluck('id', 'name');
        // ['Diameter' => x, 'Thickness' => y, 'Length' => z, ...]

        // Define dependencies using names
        $dependencies = [
            ['option' => 'Round',  'requires' => ['Diameter', 'Thickness','Height']],
            ['option' => 'Square', 'requires' => ['Size', 'Height']],
            ['option' => 'With Lid', 'requires' => ['Handle', 'Partition']],
        ];

        foreach ($dependencies as $dep) {
            if (!isset($optionIdMap[$dep['option']])) {
                $this->command->warn("⚠ Option '{$dep['option']}' not found, skipping.");
                continue;
            }

            $optionId = $optionIdMap[$dep['option']];

            foreach ($dep['requires'] as $paramName) {
                if (!isset($paramIdMap[$paramName])) {
                    $this->command->warn("⚠ Parameter '{$paramName}' not found, skipping.");
                    continue;
                }

                ParameterOptionDependency::create([
                    'option_id'             => $optionId,
                    'req_param_id' => $paramIdMap[$paramName],
                ]);
            }
        }
    }
}
