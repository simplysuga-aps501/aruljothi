<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterConfig;
use App\Models\Product\ParameterOptionConfig;
use App\Models\Product\ParameterOptionDependency;
use App\Models\Product\Template;
use App\Models\Product\ParameterUnit;


class AddNewProductSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------
        // 1️⃣ Cover template
        // ---------------------------
        $coverTemplate = Template::firstOrCreate(
            ['name' => 'cover'],
            ['abbreviation' => 'CVR', 'modified_by' => 1]
        );

        // Remove old Shape param and clean configs for cover
        $oldShape = Parameter::where('name', 'Shape')->first();
        if ($oldShape) {
            ParameterConfig::where('prod_template_id', $coverTemplate->id)
                ->where('prod_parameter_id', $oldShape->id)
                ->delete();
        }
        ParameterConfig::where('prod_template_id', $coverTemplate->id)->delete();

        // Desired order: Shape (Cover), Handle, Partition, Holes
        $coverParams = [
            'Shape (Cover)' => 'select',
            'Handle'        => 'select',
            'Partition'     => 'select',
            'Holes'         => 'select',
        ];

        foreach ($coverParams as $pname => $type) {
            $param = Parameter::firstOrCreate(['name' => $pname], [
                'input_type' => $type,
                'modified_by' => 1,
            ]);

            ParameterConfig::firstOrCreate([
                'prod_template_id'  => $coverTemplate->id,
                'prod_parameter_id' => $param->id,
            ], ['modified_by' => 1]);
        }

        // ---------------------------
        // 2️⃣ Cover parameter options
        // ---------------------------
        // Shape (Cover)
        $shapeCover = Parameter::where('name', 'Shape (Cover)')->first();
        $shapeOptions = [
            ['parameter_option' => 'Round', 'abbreviation' => 'RND'],
            ['parameter_option' => 'Square','abbreviation' => 'SQR'],
        ];

        foreach ($shapeOptions as $opt) {
            ParameterOptionConfig::firstOrCreate([
                'prod_parameter_id' => $shapeCover->id,
                'parameter_option'  => $opt['parameter_option'],
            ], ['abbreviation' => $opt['abbreviation'], 'modified_by' => 1]);
        }

        // Handle
        $handleParam = Parameter::where('name', 'Handle')->first();
        $handleOptions = [
            ['parameter_option' => 'With Handle',    'abbreviation' => 'HDL'],
            ['parameter_option' => 'Without Handle', 'abbreviation' => null],
            ['parameter_option' => 'Adjustable Handle','abbreviation'=>'ADJ']
        ];
        foreach ($handleOptions as $opt) {
            ParameterOptionConfig::firstOrCreate([
                'prod_parameter_id' => $handleParam->id,
                'parameter_option' => $opt['parameter_option'],
            ], ['abbreviation' => $opt['abbreviation'], 'modified_by' => 1]);
        }

        // Partition
        $partitionParam = Parameter::where('name', 'Partition')->first();
        $partitionOptions = [
            ['parameter_option' => 'Two Halves', 'abbreviation' => '2H'],
            ['parameter_option' => 'Single Piece', 'abbreviation' => '1P'],
        ];
        foreach ($partitionOptions as $opt) {
            ParameterOptionConfig::firstOrCreate([
                'prod_parameter_id' => $partitionParam->id,
                'parameter_option' => $opt['parameter_option'],
            ], ['abbreviation' => $opt['abbreviation'], 'modified_by' => 1]);
        }

        // Holes
        $holesParam = Parameter::where('name', 'Holes')->first();
        $holesOptions = [
            ['parameter_option' => 'With Holes', 'abbreviation' => null],
            ['parameter_option' => 'Without Holes','abbreviation'=>null],
        ];
        foreach ($holesOptions as $opt) {
            ParameterOptionConfig::firstOrCreate([
                'prod_parameter_id' => $holesParam->id,
                'parameter_option' => $opt['parameter_option'],
            ], ['abbreviation' => $opt['abbreviation'], 'modified_by' => 1]);
        }

        // ---------------------------
        // 3️⃣ Shape dependencies
        // ---------------------------
        $shapeOptionMap = ParameterOptionConfig::where('prod_parameter_id', $shapeCover->id)
            ->pluck('id','parameter_option');
        // Delete old dependencies to make reseeding safe
        ParameterOptionDependency::whereIn('option_id', $shapeOptionMap)->delete();

        $shapeDependencies = [
            'Round'  => ['Diameter', 'Thickness'],
            'Square' => ['Size'],
        ];

        $paramMap = Parameter::pluck('id','name');

        foreach ($shapeDependencies as $optionName => $reqParams) {
            if (!isset($shapeOptionMap[$optionName])) continue;
            foreach ($reqParams as $pname) {
                if (!isset($paramMap[$pname])) continue;
                ParameterOptionDependency::firstOrCreate([
                    'option_id'    => $shapeOptionMap[$optionName],
                    'req_param_id' => $paramMap[$pname],
                ]);
            }
        }

        $this->command->info("✅ Cover template updated with Shape (Cover), Handle, Partition, Holes and dependencies.");

        // Ensure Breadth parameter exists
        $breadthParam = Parameter::firstOrCreate(
            ['name' => 'Breadth'],
            [
                'input_type'  => 'number',
                'description' => 'Breadth',
                'unit'        => 'IN', // Default for global usage
                'modified_by' => 1
            ]
        );

        // ---------------------------
        // 🔹 Backfill existing ParameterConfig for all templates
        // ---------------------------

        // First, get all existing parameter units
        $unitMap = ParameterUnit::pluck('id', 'unit')->toArray(); // ['MM' => 1, 'IN' => 2, 'FT' => 3]

        $allConfigs = ParameterConfig::all();

        foreach ($allConfigs as $config) {
            $param = Parameter::find($config->prod_parameter_id);
            if (!$param) continue;

            $updated = false;

            // 1️⃣ Map the string unit to parameter_units table
            if (is_null($config->unit_id) && !empty($param->unit)) {
                if (isset($unitMap[$param->unit])) {
                    $config->unit_id = $unitMap[$param->unit];
                    $updated = true;
                } else {
                    // If the unit string does not exist, create it
                    $newUnit = ParameterUnit::create([    'prod_parameter_id' => $param->id, 'unit' => $param->unit, 'modified_by' => 1]);
                    $unitMap[$param->unit] = $newUnit->id;
                    $config->unit_id = $newUnit->id;
                    $updated = true;
                }
            }

            // 2️⃣ Ensure allow_custom_unit has a default value
            if (is_null($config->allow_custom_unit)) {
                $config->allow_custom_unit = false;
                $updated = true;
            }

            if ($updated) {
                $config->modified_by = 1;
                $config->save();
            }
        }

        // First, get or create all units we need
        $unitsToEnsure = ['MM', 'IN', 'FT'];
        $unitMap = ParameterUnit::whereIn('unit', $unitsToEnsure)
            ->pluck('id', 'unit')
            ->toArray();

        // Create missing units
        foreach ($unitsToEnsure as $unitName) {
            if (!isset($unitMap[$unitName])) {
                $newUnit = ParameterUnit::create(['unit' => $unitName, 'modified_by' => 1]);
                $unitMap[$unitName] = $newUnit->id;
            }
        }

        // ---------------------------
        // 4️⃣ New product: Kerb Stones
        // ---------------------------
        $kerbTemplate = Template::firstOrCreate(
            ['name' => 'Kerb Stones'],
            ['abbreviation' => 'KBS', 'modified_by' => 1]
        );

        $kerbUnits = [
            'Length' => 'MM',
            'Breadth' => 'MM',
            'Height' => 'MM',
        ];

        foreach ($kerbUnits as $pname => $unit) {
            $param = Parameter::where('name', $pname)->first();
            if ($param && isset($unitMap[$unit])) {
                ParameterConfig::updateOrCreate(
                    [
                        'prod_template_id' => $kerbTemplate->id,
                        'prod_parameter_id'=> $param->id,
                    ],
                    [
                        'unit_id' => $unitMap[$unit],   // ✅ Save unit ID, not string
                        'allow_custom_unit' => false,
                        'modified_by'=>1
                    ]
                );
            }
        }

        // ---------------------------
        // 5️⃣ New product: Cement Pillars
        // ---------------------------
        $pillarTemplate = Template::firstOrCreate(
            ['name' => 'Cement Pillars'],
            ['abbreviation' => 'CP', 'modified_by' => 1]
        );

        $pillarUnits = [
            'Length'  => 'IN',
            'Breadth' => 'IN',
            'Height'  => 'FT',
        ];

        foreach ($pillarUnits as $pname => $unit) {
            $param = Parameter::where('name', $pname)->first();
            if ($param && isset($unitMap[$unit])) {
                ParameterConfig::updateOrCreate(
                    [
                        'prod_template_id' => $pillarTemplate->id,
                        'prod_parameter_id'=> $param->id,
                    ],
                    [
                        'unit_id' => $unitMap[$unit],  // ✅ Save unit ID
                        'allow_custom_unit' => false,
                        'modified_by'=>1
                    ]
                );
            }
        }
        // ---------------------------
        // 6️⃣ Cement Pillar Type Parameter
        // ---------------------------
        $typeParam = Parameter::firstOrCreate(
            ['name' => 'Type'],
            [
                'description' => 'Type of Cement Pillar',
                'input_type' => 'select',
                'modified_by' => 1,
            ]
        );

        // ✅ Add the possible options
        $typeOptions = [
            ['parameter_option' => 'Plain End',     'abbreviation' => 'PE'],
            ['parameter_option' => 'U Shaped Top',  'abbreviation' => 'UST'],
            ['parameter_option' => 'With Rod',      'abbreviation' => 'WR'],
        ];

        foreach ($typeOptions as $opt) {
            ParameterOptionConfig::firstOrCreate(
                [
                    'prod_parameter_id' => $typeParam->id,
                    'parameter_option'  => $opt['parameter_option'],
                ],
                [
                    'abbreviation' => $opt['abbreviation'],
                    'modified_by'  => 1,
                ]
            );
        }

        // ✅ Link the parameter to Cement Pillars template
        ParameterConfig::updateOrCreate(
            [
                'prod_template_id'  => $pillarTemplate->id,
                'prod_parameter_id' => $typeParam->id,
            ],
            [
                'allow_custom_unit' => false,
                'modified_by' => 1,
            ]
        );

        $this->command->info("✅ New products added: Kerb Stones (mm) and Cement Pillars (in/ft units).");

    }
}
