<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterConfig;
use App\Models\Product\Template;

class ParameterConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Structured config array with input_type
        $configs = [
            [
                'template' => 'rcc pipe',
                'parameters' => [
                    ['name' => 'Diameter',   'input_type' => 'numeric'],
                    ['name' => 'Length',     'input_type' => 'numeric'],
                    ['name' => 'Class',      'input_type' => 'select'],
                    ['name' => 'Pipe Type',  'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'offcut rcc pipe',
                'parameters' => [
                    ['name' => 'Diameter', 'input_type' => 'numeric'],
                    ['name' => 'Length',   'input_type' => 'numeric'],
                    ['name' => 'Class',    'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'chamber',
                'parameters' => [
                    ['name' => 'Shape', 'input_type' => 'select'],
                    ['name' => 'Cover', 'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'ring',
                'parameters' => [
                    ['name' => 'Diameter',  'input_type' => 'numeric'],
                    ['name' => 'Thickness', 'input_type' => 'numeric'],
                    ['name' => 'Height',    'input_type' => 'numeric'],
                    ['name' => 'Cover',     'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'v trough',
                'parameters' => [
                    ['name' => 'Voltage',   'input_type' => 'numeric'],
                    ['name' => 'Length',       'input_type' => 'numeric'],
                    ['name' => 'Thickness', 'input_type' => 'numeric'],
                ],
            ],
            [
                'template' => 'water tank',
                'parameters' => [
                    ['name' => 'Shape',   'input_type' => 'select'],
                    ['name' => 'Capacity','input_type' => 'numeric'],
                    ['name' => 'Class',   'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'cover',
                'parameters' => [
                    ['name' => 'Shape', 'input_type' => 'select'],
                    ['name' => 'Handle',       'input_type' => 'select'],
                    ['name' => 'Partition',    'input_type' => 'select'],
                    ['name' => 'Holes',        'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'manhole cover',
                'parameters' => [
                    ['name' => 'Size', 'input_type' => 'numeric'],
                    ['name' => 'Grade',    'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'cement pillar',
                'parameters' => [
                    ['name' => 'Length',      'input_type' => 'numeric'],
                    ['name' => 'Breadth',     'input_type' => 'numeric'],
                    ['name' => 'Height',      'input_type' => 'numeric'],
                    ['name' => 'Pillar Type', 'input_type' => 'select'],
                ],
            ],
            [
                'template' => 'kerb stone',
                'parameters' => [
                    ['name' => 'Length',     'input_type' => 'numeric'],
                    ['name' => 'Breadth',    'input_type' => 'numeric'],
                    ['name' => 'Height',     'input_type' => 'numeric'],
                    ['name' => 'Stone Type', 'input_type' => 'select'],
                ],
            ],
        ];

        // Lookup maps for template and parameter IDs
        $paramIdMap    = Parameter::pluck('id', 'name')->toArray();
        $templateIdMap = Template::pluck('id', 'name')->toArray();

        // Loop through templates and parameters
        foreach ($configs as $config) {
            $templateName = $config['template'];
            if (!isset($templateIdMap[$templateName])) continue;

            $templateId = $templateIdMap[$templateName];
            $sortOrder = 1;

            foreach ($config['parameters'] as $param) {
                $paramName = $param['name'];
                if (!isset($paramIdMap[$paramName])) continue;

                $inputType = $param['input_type'] ?? 'select'; // fallback

                ParameterConfig::updateOrCreate(
                    [
                        'prod_template_id'  => $templateId,
                        'prod_parameter_id' => $paramIdMap[$paramName],
                    ],
                    [
                        'modified_by'   => 1,
                        'sort_order'    => $sortOrder++,
                        'is_required'   => $param['is_required'] ?? true,
                        'default_value' => $param['default_value'] ?? null,
                        'input_type'    => $inputType,
                        'group_name'    => $param['group_name'] ?? null,
                    ]
                );
            }
        }
    }
}
