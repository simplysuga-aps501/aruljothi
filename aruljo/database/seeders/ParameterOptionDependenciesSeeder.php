<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ParameterOptionDependency;
use App\Models\Product\ParameterOptionConfig;
use App\Models\Product\Parameter;
use App\Models\Product\Template;

class ParameterOptionDependenciesSeeder extends Seeder
{
    public function run(): void
    {
        // Map template names to IDs
        $templates = Template::pluck('id', 'name')->all();

        $dependenciesData = [
            'cover' => [
                ['option' => 'Round',    'requires' => ['Diameter', 'Thickness']],
                ['option' => 'Square',   'requires' => ['Size', 'Thickness']],
            ],
            'chamber' => [
                ['option' => 'Round',  'requires' => ['Diameter', 'Thickness','Height']],
                ['option' => 'Square', 'requires' => ['Size', 'Thickness', 'Height']],
                ['option' => 'With Lid', 'requires' => ['Handle', 'Partition', 'Holes']],
            ],
            'water tank' => [
                ['option' => 'Round',  'requires' => ['Diameter', 'Thickness','Height']],
                ['option' => 'Square', 'requires' => ['Size', 'Thickness', 'Height']],
            ],
            'ring' => [
                ['option' => 'With Lid', 'requires' => ['Handle', 'Partition', 'Holes']],
            ],
        ];

        foreach ($dependenciesData as $templateName => $deps) {
            $templateId = $templates[$templateName] ?? null;
            if (!$templateId) {
                $this->command->warn("Template '{$templateName}' not found, skipping...");
                continue;
            }

            foreach ($deps as $dep) {
                // Find the option linked to the template
                $option = ParameterOptionConfig::where('parameter_option', $dep['option'])
                    ->where('prod_template_id', $templateId)
                    ->first();

                if (!$option) {
                    $this->command->warn("Option '{$dep['option']}' not found for template '{$templateName}', skipping.");
                    continue;
                }

                foreach ($dep['requires'] as $paramName) {
                    // Fetch global parameter
                    $param = Parameter::where('name', $paramName)->first();
                    if (!$param) {
                        $this->command->warn("Parameter '{$paramName}' not found, skipping.");
                        continue;
                    }

                    // Insert dependency
                    ParameterOptionDependency::firstOrCreate([
                        'option_id'       => $option->id,
                        'req_param_id'    => $param->id,
                        'prod_template_id'=> $templateId,
                    ]);
                }
            }
        }

        $this->command->info("✅ All template-specific parameter option dependencies seeded successfully.");
    }
}
