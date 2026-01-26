<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterConfig;
use App\Models\Product\ParameterOptionConfig;
use App\Models\Product\ParameterOptionDependency;
use App\Models\Product\Template;

class AddParameterCoverShapeSeeder extends Seeder
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

        // ---------------------------
        // 4️⃣ New product: Kerb Stones
        // ---------------------------
        $kerbTemplate = Template::firstOrCreate(
            ['name' => 'Kerb Stones'],
            ['abbreviation' => 'KBS', 'modified_by' => 1]
        );

        foreach (['Length','Width','Height'] as $pname) {
            $param = Parameter::where('name',$pname)->first();
            if ($param) {
                ParameterConfig::firstOrCreate([
                    'prod_template_id' => $kerbTemplate->id,
                    'prod_parameter_id'=> $param->id,
                ],['modified_by'=>1]);
            }
        }

        // ---------------------------
        // 5️⃣ New product: Cement Pillars
        // ---------------------------
        $pillarTemplate = Template::firstOrCreate(
            ['name' => 'Cement Pillars'],
            ['abbreviation' => 'CP', 'modified_by' => 1]
        );
        // ---------------------------
        // Ensure Breadth parameter exists
        // ---------------------------
        $breadthParam = Parameter::firstOrCreate(
            ['name' => 'Breadth'],
            [
                'input_type'  => 'number',
                'description' => 'Breadth',
                'unit'        => 'MM',
                'modified_by' => 1
            ]
        );

        // ---------------------------
        // Attach Breadth to Kerb Stones
        // ---------------------------
        $kerbTemplate = Template::firstOrCreate(
            ['name' => 'Kerb Stones'],
            ['abbreviation' => 'KBS', 'modified_by' => 1]
        );

        foreach (['Length','Breadth','Height'] as $pname) {
            $param = Parameter::where('name', $pname)->first();
            if ($param) {
                ParameterConfig::firstOrCreate([
                    'prod_template_id' => $kerbTemplate->id,
                    'prod_parameter_id'=> $param->id,
                ], ['modified_by'=>1]);
            }
        }


        // Type options for Cement Pillars
        $typeParam = Parameter::firstOrCreate(
            ['name' => 'Type'],
            ['input_type' => 'select', 'modified_by' => 1]
        );
        $typeOptions = ['Plain End','With Rod','U-shaped Top'];
        foreach ($typeOptions as $opt) {
            ParameterOptionConfig::firstOrCreate([
                'prod_parameter_id' => $typeParam->id,
                'parameter_option' => $opt,
            ], ['modified_by'=>1]);
        }

        $this->command->info("✅ New products added: Kerb Stones and Cement Pillars.");
    }
}
