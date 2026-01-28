<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterConfig;
use App\Models\Product\template;

class ParameterConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['template' => 'rcc pipe',       'parameters' => ['Diameter', 'Length', 'Class', 'Pipe Type']],
            ['template' => 'offcut rcc pipe',     'parameters' => ['Diameter', 'Length', 'Class']],
            ['template' => 'chamber',    'parameters' => ['Shape', 'Cover']],
            ['template' => 'ring',       'parameters' => ['Diameter', 'Thickness', 'Height', 'Cover']],
            ['template' => 'v trough',    'parameters' => ['Voltage', 'Len', 'Thickness']],
            ['template' => 'water tank', 'parameters' => ['Shape','Capacity', 'Class']],
            ['template' => 'cover',      'parameters' => ['Shape(Cover)','Handle', 'Partition','Holes']],
            ['template' => 'manhole cover',   'parameters' => ['MHC_Size', 'Grade']],
            ['template' => 'cement pillar',     'parameters' => ['Length', 'Breadth', 'Height','Pillar Type']],
            ['template' => 'kerb stone',     'parameters' => ['Length', 'Breadth', 'Height','Stone Type']],
        ];

        // Build maps
        $paramIdMap    = Parameter::pluck('id', 'name')->toArray();   // ['Diameter' => 1, ...]
        $templateIdMap = template::pluck('id', 'name')->toArray();    // ['pipe' => 1, ...]

         // 🧹 Delete old Shape parameter from Cover template
        $coverTemplateId = Template::where('name', 'cover')->value('id');
        $shapeParamId = Parameter::where('name', 'Shape')->value('id');
        if ($coverTemplateId && $shapeParamId) {
            ParameterConfig::where('prod_template_id', $coverTemplateId)
                ->where('prod_parameter_id', $shapeParamId)
                ->delete();
        }

        foreach ($configs as $config) {
            $templateName = $config['template'];

            if (!isset($templateIdMap[$templateName])) {
                // Skip if template not found
                continue;
            }

            foreach ($config['parameters'] as $paramName) {
                if (!isset($paramIdMap[$paramName])) {
                    // Skip if parameter not found
                    continue;
                }

                ParameterConfig::updateOrCreate(
                    [
                        'prod_template_id'  => $templateIdMap[$templateName],
                        'prod_parameter_id' => $paramIdMap[$paramName],
                    ],
                    [
                        'modified_by' => 1,
                    ]
                );
            }
        }
    }
}
