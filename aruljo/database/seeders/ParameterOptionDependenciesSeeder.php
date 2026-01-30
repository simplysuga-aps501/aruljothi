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

        // Each option can require specific parameters, some required and some optional
        $dependenciesData = [
            'cover' => [
                [
                    'option' => 'Round',
                    'requires' => [
                        ['param' => 'Diameter', 'is_required' => true],
                        ['param' => 'Thickness', 'is_required' => true],
                    ],
                ],
                [
                    'option' => 'Square',
                    'requires' => [
                        ['param' => 'Size', 'is_required' => true],
                        ['param' => 'Thickness', 'is_required' => true],
                    ],
                ],
            ],

            'chamber' => [
                [
                    'option' => 'Round',
                    'requires' => [
                        ['param' => 'Diameter', 'is_required' => true],
                        ['param' => 'Thickness', 'is_required' => true],
                        ['param' => 'Height', 'is_required' => true],
                    ],
                ],
                [
                    'option' => 'Square',
                    'requires' => [
                        ['param' => 'Size', 'is_required' => true],
                        ['param' => 'Thickness', 'is_required' => true],
                        ['param' => 'Height', 'is_required' => true],
                    ],
                ],
                [
                    'option' => 'With Lid',
                    'requires' => [
                        ['param' => 'Handle', 'is_required' => true],
                        ['param' => 'Partition', 'is_required' => true],
                        ['param' => 'Holes', 'is_required' => true],
                    ],
                ],
            ],

            'water tank' => [
                [
                    'option' => 'Round',
                    'requires' => [
                        ['param' => 'Diameter', 'is_required' => true],
                        ['param' => 'Thickness', 'is_required' => true],
                        ['param' => 'Height', 'is_required' => true],
                    ],
                ],
                [
                    'option' => 'Square',
                    'requires' => [
                        ['param' => 'Size', 'is_required' => true],
                        ['param' => 'Thickness', 'is_required' => true],
                        ['param' => 'Height', 'is_required' => true],
                    ],
                ],
            ],

            'ring' => [
                [
                    'option' => 'With Lid',
                    'requires' => [
                        ['param' => 'Handle', 'is_required' => true],
                        ['param' => 'Partition', 'is_required' => true],
                        ['param' => 'Holes', 'is_required' => true],
                    ],
                ],
            ],
        ];

        foreach ($dependenciesData as $templateName => $deps) {
            $templateId = $templates[$templateName] ?? null;
            if (!$templateId) {
                $this->command->warn("Template '{$templateName}' not found, skipping...");
                continue;
            }

            foreach ($deps as $dep) {
                $option = ParameterOptionConfig::where('parameter_option', $dep['option'])
                    ->where('prod_template_id', $templateId)
                    ->first();

                if (!$option) {
                    $this->command->warn("Option '{$dep['option']}' not found for template '{$templateName}', skipping.");
                    continue;
                }

                foreach ($dep['requires'] as $req) {
                    $paramName   = $req['param'];
                    $isRequired  = $req['is_required'] ?? true;

                    $param = Parameter::where('name', $paramName)->first();
                    if (!$param) {
                        $this->command->warn("Parameter '{$paramName}' not found, skipping.");
                        continue;
                    }

                    ParameterOptionDependency::firstOrCreate(
                        [
                            'option_id'        => $option->id,
                            'req_param_id'     => $param->id,
                            'prod_template_id' => $templateId,
                        ],
                        [
                            'is_required' => $isRequired,
                        ]
                    );
                }
            }
        }

        $this->command->info("✅ All parameter option dependencies seeded with is_required flags successfully.");
    }
}
