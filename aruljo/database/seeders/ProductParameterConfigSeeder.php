<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameterConfig;

class ProductParameterConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['template_id' => 1, 'parameters' => ['Diameter', 'Length', 'Class', 'Pipe Type']],
            ['template_id' => 2, 'parameters' => ['Diameter', 'Length', 'Class']],
            ['template_id' => 3, 'parameters' => ['Shape', 'Cover']],
            ['template_id' => 4, 'parameters' => ['Diameter', 'Height', 'Thickness', 'Cover']],
            ['template_id' => 5, 'parameters' => ['KV', 'Length', 'Thickness']],
            ['template_id' => 6, 'parameters' => ['Shape','Capacity', 'Diameter', 'Length', 'Height', 'Thickness', 'Class']],
            ['template_id' => 7, 'parameters' => ['Shape','Handle', 'Partition']],
        ];

        $paramIdMap = \App\Models\Product\ProductParameter::pluck('id', 'name'); // ['Diameter' => 1, ...]

        foreach ($configs as $config) {
            foreach ($config['parameters'] as $paramName) {
                ProductParameterConfig::create([
                    'product_template_id' => $config['template_id'],
                    'product_parameter_id' => $paramIdMap[$paramName],
                    'modified_by' => 1,
                ]);
            }
        }
    }
}
