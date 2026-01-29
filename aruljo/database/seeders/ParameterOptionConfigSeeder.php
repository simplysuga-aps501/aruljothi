<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterOptionConfig;
use App\Models\Product\Template;
use Carbon\Carbon;

class ParameterOptionConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::parse('2025-07-22 10:00:00');

        // Map templates by name for readability
        $templates = Template::pluck('id', 'name')->all();

        // Define options per template
        $templatesData = [
            'rcc pipe' => [
                'parameters' => [
                    [
                        'name' => 'Pipe Type',
                        'options' => [
                            ['option' => 'Plain', 'abbreviation' => 'PLN'],
                            ['option' => 'Spygot', 'abbreviation' => 'SPG'],
                            ['option' => 'Male Female', 'abbreviation' => 'MF'],
                            ['option' => 'Plain End With Separate Collar', 'abbreviation' => 'PEWC'],
                        ],
                    ],
                    [
                        'name' => 'Class',
                        'options' => [
                            ['option' => 'NP2', 'abbreviation' => 'NP2'],
                            ['option' => 'NP3', 'abbreviation' => 'NP3'],
                            ['option' => 'NP4', 'abbreviation' => 'NP4'],
                        ],
                    ],
                ],
            ],
            'offcut rcc pipe' => [
                'parameters' => [
                    [
                        'name' => 'Class',
                        'options' => [
                            ['option' => 'NP2', 'abbreviation' => 'NP2'],
                            ['option' => 'NP3', 'abbreviation' => 'NP3'],
                            ['option' => 'NP4', 'abbreviation' => 'NP4'],
                        ],
                    ],
                ],
            ],

            'chamber' => [
                'parameters' => [
                    [
                        'name' => 'Shape',
                        'options' => [
                            ['option' => 'Round', 'abbreviation' => 'RND'],
                            ['option' => 'Square', 'abbreviation' => 'SQR'],
                        ],
                    ],
                    [
                        'name' => 'Cover',
                        'options' => [
                            ['option' => 'With Lid', 'abbreviation' => 'LID'],
                            ['option' => 'Without Lid', 'abbreviation' => null],
                        ],
                    ],
                    [
                        'name' => 'Handle',
                        'options' => [
                            ['option' => 'With Handle', 'abbreviation' => 'HDL'],
                            ['option' => 'Without Handle', 'abbreviation' => null],
                            ['option' => 'Adjustable Handle', 'abbreviation' => 'AHDL'],
                        ],
                    ],

                    [
                        'name' => 'Partition',
                        'options' => [
                            ['option' => 'Two Halves', 'abbreviation' => '2H'],
                            ['option' => 'Single Piece', 'abbreviation' => '1P'],
                        ],
                    ],

                    [
                        'name' => 'Holes',
                        'options' => [
                            ['option' => 'With Holes', 'abbreviation' => 'HOLE'],
                            ['option' => 'Without Holes', 'abbreviation' => null],
                        ],
                    ],
                ],
            ],

            'cover' => [
                'parameters' => [
                    [
                        'name' => 'Shape',
                        'options' => [
                            ['option' => 'Round', 'abbreviation' => 'RND'],
                            ['option' => 'Square', 'abbreviation' => 'SQR'],
                        ],
                    ],

                    [
                        'name' => 'Handle',
                        'options' => [
                            ['option' => 'With Handle', 'abbreviation' => 'HDL'],
                            ['option' => 'Without Handle', 'abbreviation' => null],
                            ['option' => 'Adjustable Handle', 'abbreviation' => 'AHDL'],
                        ],
                    ],

                    [
                        'name' => 'Partition',
                        'options' => [
                            ['option' => 'Two Halves', 'abbreviation' => '2H'],
                            ['option' => 'Single Piece', 'abbreviation' => '1P'],
                        ],
                    ],

                    [
                        'name' => 'Holes',
                        'options' => [
                            ['option' => 'With Holes', 'abbreviation' => 'HOLE'],
                            ['option' => 'Without Holes', 'abbreviation' => null],
                        ],
                    ],

                ],
            ],
            'ring' => [
                'parameters' => [

                    [
                        'name' => 'Cover',
                        'options' => [
                            ['option' => 'With Lid', 'abbreviation' => 'LID'],
                            ['option' => 'Without Lid', 'abbreviation' => null],
                        ],
                    ],
                    [
                        'name' => 'Handle',
                        'options' => [
                            ['option' => 'With Handle', 'abbreviation' => 'HDL'],
                            ['option' => 'Without Handle', 'abbreviation' => null],
                            ['option' => 'Adjustable Handle', 'abbreviation' => 'AHDL'],
                        ],
                    ],

                    [
                        'name' => 'Partition',
                        'options' => [
                            ['option' => 'Two Halves', 'abbreviation' => '2H'],
                            ['option' => 'Single Piece', 'abbreviation' => '1P'],
                        ],
                    ],

                    [
                        'name' => 'Holes',
                        'options' => [
                            ['option' => 'With Holes', 'abbreviation' => 'HOLE'],
                            ['option' => 'Without Holes', 'abbreviation' => null],
                        ],
                    ],

                ],
            ],
            'water tank' => [
                'parameters' => [
                    [
                        'name' => 'Shape',
                        'options' => [
                            ['option' => 'Round', 'abbreviation' => 'RND'],
                            ['option' => 'Square', 'abbreviation' => 'SQR'],
                        ],
                    ],
                    [
                        'name' => 'Cover',
                        'options' => [
                            ['option' => 'With Lid', 'abbreviation' => 'LID'],
                            ['option' => 'Without Lid', 'abbreviation' => null],
                        ],
                    ],
                    [
                        'name' => 'Handle',
                        'options' => [
                            ['option' => 'With Handle', 'abbreviation' => 'HDL'],
                            ['option' => 'Without Handle', 'abbreviation' => null],
                            ['option' => 'Adjustable Handle', 'abbreviation' => 'AHDL'],
                        ],
                    ],

                    [
                        'name' => 'Partition',
                        'options' => [
                            ['option' => 'Two Halves', 'abbreviation' => '2H'],
                            ['option' => 'Single Piece', 'abbreviation' => '1P'],
                        ],
                    ],

                    [
                        'name' => 'Holes',
                        'options' => [
                            ['option' => 'With Holes', 'abbreviation' => 'HOLE'],
                            ['option' => 'Without Holes', 'abbreviation' => null],
                        ],
                    ],
                    [
                        'name' => 'Class',
                        'options' => [
                            ['option' => 'NP2', 'abbreviation' => 'NP2'],
                            ['option' => 'NP3', 'abbreviation' => 'NP3'],
                            ['option' => 'NP4', 'abbreviation' => 'NP4'],
                        ],
                    ],

                ],
            ],
            'manhole cover' => [
                'parameters' => [
                    [
                        'name' => 'Grade',
                        'options' => [
                            ['option' => 'Medium Duty',      'abbreviation' => 'MD'],
                            ['option' => 'Heavy Duty',       'abbreviation' => 'HD'],
                            ['option' => 'Extra Heavy Duty', 'abbreviation' => 'EHD'],
                        ],
                    ],
                ],
            ],
            'kerb stone' => [
                'parameters' => [
                    [
                        'name' => 'Stone Type',
                        'options' => [
                            ['option' => 'Onida', 'abbreviation' => 'ONI'],
                            ['option' => 'Rectangle', 'abbreviation' => 'REC'],
                        ],
                    ],
                ],
            ],

            'cement pillar' => [
                'parameters' => [
                    [
                        'name' => 'Pillar Type',
                        'options' => [
                            ['option' => 'Plain End', 'abbreviation' => 'PE'],
                            ['option' => 'U Shaped Top', 'abbreviation' => 'UT'],
                            ['option' => 'With Rod', 'abbreviation' => 'ROD'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($templatesData as $templateName => $templateInfo) {
            $templateId = $templates[$templateName] ?? null;

            if (!$templateId) {
                $this->command->warn("Template '{$templateName}' not found. Skipping...");
                continue;
            }

            foreach ($templateInfo['parameters'] as $paramData) {
                // Fetch global parameter by name
                $parameter = Parameter::where('name', $paramData['name'])->first();
                if (!$parameter) {
                    $this->command->warn("Parameter '{$paramData['name']}' not found, skipping.");
                    continue;
                }

                if (!empty($paramData['options'])) {
                    foreach ($paramData['options'] as $opt) {
                        ParameterOptionConfig::firstOrCreate(
                            [
                                'prod_parameter_id' => $parameter->id,
                                'parameter_option'  => $opt['option'],
                                'prod_template_id'  => $templateId,
                            ],
                            [
                                'abbreviation' => $opt['abbreviation'] ?? null,
                                'is_active'    => true,
                                'modified_by'  => 1,
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info("✅ Parameter options seeded successfully.");
    }
}
