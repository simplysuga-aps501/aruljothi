<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Template;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterUnit;
use App\Models\Product\ParameterUnitConfig;
use Carbon\Carbon;

class ParameterUnitConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Hard-coded mapping
        $configs = [
            ['template' => 'rcc pipe',        'parameters' => ['Diameter'=>'MM', 'Length'=>'MTR']],
            ['template' => 'offcut rcc pipe', 'parameters' => ['Diameter'=>'MM', 'Length'=>'MTR']],
            ['template' => 'chamber',         'parameters' => ['Diameter'=>'MM', 'Thickness'=>'MM', 'Height'=>'MM', 'Size'=>'MM']],
            ['template' => 'ring',            'parameters' => ['Diameter'=>'MM', 'Thickness'=>'MM', 'Height'=>'MM']],
            ['template' => 'v trough',        'parameters' => ['Voltage'=>'KV', 'Length'=>'MM', 'Thickness'=>'MM']],
            ['template' => 'water tank',      'parameters' => ['Capacity'=>'LTR','Diameter'=>'MM', 'Thickness'=>'MM', 'Height'=>'MM' ,'Size'=>'MM' ]],
            ['template' => 'cover',           'parameters' => ['Diameter'=>'MM', 'Thickness'=>'MM', 'Size'=>'MM']],
            ['template' => 'manhole cover',   'parameters' => ['Size'=>'MM']],
            ['template' => 'cement pillar',   'parameters' => ['Length'=>'IN', 'Breadth'=>'IN','Height'=>'FT',]],
            ['template' => 'kerb stone',   'parameters' => ['Length'=>'MM', 'Thickness'=>'MM','Height'=>'MM',]],
        ];


        foreach ($configs as $config) {
            $template = Template::where('name', $config['template'])->first();
            if (!$template) continue;

            foreach ($config['parameters'] as $paramName => $unitAbbr) {
                $param = Parameter::where('name', $paramName)->first();
                if (!$param) continue;

                $unitId = $unitAbbr ? ParameterUnit::where('unit', $unitAbbr)->value('id') : null;

                ParameterUnitConfig::updateOrCreate(
                    [
                        'prod_template_id'  => $template->id,
                        'prod_parameter_id' => $param->id,
                    ],
                    [
                        'prod_parameter_unit_id'           => $unitId,
                        'allow_custom_unit' => 0,
                        'modified_by'       => 1,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ]
                );
            }
        }

        $this->command->info("✅ ParameterUnitConfig seeder executed successfully.");
    }
}
