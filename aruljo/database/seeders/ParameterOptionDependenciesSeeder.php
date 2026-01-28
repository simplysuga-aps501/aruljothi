<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ParameterOptionDependency;
use App\Models\Product\ParameterOptionConfig;
use App\Models\Product\Parameter;

class ParameterOptionDependenciesSeeder extends Seeder
{
    public function run(): void
    {
        // Hardcoded dependencies by names
        $dependencies = [
            // Cover template
            ['option' => 'Round',           'requires' => ['Diameter', 'Thickness', 'Height']],
            ['option' => 'Square',          'requires' => ['Size', 'Thickness','Height']],
            ['option' => 'With Lid',        'requires' => ['Handle', 'Partition','Holes']],
            ['option' => 'Round(C)',     'requires' => ['Diameter', 'Thickness']],
            ['option' => 'Square(C)',     'requires' => ['Size', 'Thickness']],

        ];

        // Loop through each dependency and save
        foreach ($dependencies as $dep) {
            $option = ParameterOptionConfig::where('parameter_option', $dep['option'])->first();
            if (!$option) {
                $this->command->warn("⚠ Option '{$dep['option']}' not found, skipping.");
                continue;
            }

            foreach ($dep['requires'] as $paramName) {
                $param = Parameter::where('name', $paramName)->first();
                if (!$param) {
                    $this->command->warn("⚠ Parameter '{$paramName}' not found, skipping.");
                    continue;
                }

                ParameterOptionDependency::updateOrCreate([
                    'option_id'    => $option->id,
                    'req_param_id' => $param->id,
                ]);
            }
        }

        $this->command->info("✅ All parameter option dependencies seeded successfully.");
    }
}
