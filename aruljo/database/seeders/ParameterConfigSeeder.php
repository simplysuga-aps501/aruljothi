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
            ['template' => 'vtrough',    'parameters' => ['KV', 'Length', 'Thickness']],
            ['template' => 'water tank', 'parameters' => ['Shape','Capacity', 'Class']],
            ['template' => 'cover',      'parameters' => ['Shape','Handle', 'Partition']],
        ];

        // Build maps
        $paramIdMap    = Parameter::pluck('id', 'name')->toArray();   // ['Diameter' => 1, ...]
        $templateIdMap = template::pluck('id', 'name')->toArray();    // ['pipe' => 1, ...]

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

                ParameterConfig::create([
                    'prod_template_id'  => $templateIdMap[$templateName], // resolved ID
                    'prod_parameter_id' => $paramIdMap[$paramName],       // resolved ID
                    'modified_by'          => 1,
                ]);
            }
        }
    }
}
