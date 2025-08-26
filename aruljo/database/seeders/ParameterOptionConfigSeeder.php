<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterOptionConfig;
use Carbon\Carbon;

class ParameterOptionConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::parse('2025-07-22 10:00:00');

        // Notice we use 'parameter_name' instead of ID
        $options = [
            ['parameter_name' => 'Shape', 'parameter_option' => 'Round'],
            ['parameter_name' => 'Shape', 'parameter_option' => 'Square'],
            ['parameter_name' => 'Cover', 'parameter_option' => 'With Lid'],
            ['parameter_name' => 'Cover', 'parameter_option' => 'Without Lid'],
            ['parameter_name' => 'Class', 'parameter_option' => 'NP2'],
            ['parameter_name' => 'Class', 'parameter_option' => 'NP3'],
            ['parameter_name' => 'Class', 'parameter_option' => 'NP4'],
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Plain'],
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Spygot'],
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Male Female'],
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Plain End With Separate Collar'],
            ['parameter_name' => 'Handle', 'parameter_option' => 'With Handle'],
            ['parameter_name' => 'Handle', 'parameter_option' => 'Without Handle'],
            ['parameter_name' => 'Partition', 'parameter_option' => 'Two Halves'],
            ['parameter_name' => 'Partition', 'parameter_option' => 'Single Piece'],
        ];

        foreach ($options as $entry) {
            $parameter = Parameter::where('name', $entry['parameter_name'])->first();

            if ($parameter) {
                ParameterOptionConfig::create([
                    'prod_parameter_id' => $parameter->id,
                    'parameter_option'     => $entry['parameter_option'],
                    'modified_by'          => null,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }
        }
    }
}
