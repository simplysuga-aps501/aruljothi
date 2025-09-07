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

        $options = [
            // Shape
            ['parameter_name' => 'Shape', 'parameter_option' => 'Round',  'abbreviation' => 'RND'],
            ['parameter_name' => 'Shape', 'parameter_option' => 'Square', 'abbreviation' => 'SQR'],

            // Cover
            ['parameter_name' => 'Cover', 'parameter_option' => 'With Lid',    'abbreviation' => 'LID'],
            ['parameter_name' => 'Cover', 'parameter_option' => 'Without Lid', 'abbreviation' => null],

            // Class (no abbreviations — keep same as option)
            ['parameter_name' => 'Class', 'parameter_option' => 'NP2', 'abbreviation' => 'NP2'],
            ['parameter_name' => 'Class', 'parameter_option' => 'NP3', 'abbreviation' => 'NP3'],
            ['parameter_name' => 'Class', 'parameter_option' => 'NP4', 'abbreviation' => 'NP4'],

            // Pipe Type
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Plain',                        'abbreviation' => 'PLN'],
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Spygot',                       'abbreviation' => 'SPG'],
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Male Female',                  'abbreviation' => 'MF'],
            ['parameter_name' => 'Pipe Type', 'parameter_option' => 'Plain End With Separate Collar','abbreviation' => 'PEWC'],

            // Handle
            ['parameter_name' => 'Handle', 'parameter_option' => 'With Handle',    'abbreviation' => 'HDL'],
            ['parameter_name' => 'Handle', 'parameter_option' => 'Without Handle', 'abbreviation' => null],

            // Partition
            ['parameter_name' => 'Partition', 'parameter_option' => 'Two Halves',   'abbreviation' => '2H'],
            ['parameter_name' => 'Partition', 'parameter_option' => 'Single Piece', 'abbreviation' => '1P'],

            // Strength for Manhole Cover
            ['parameter_name' => 'Grade', 'parameter_option' => 'Medium Duty',      'abbreviation' => 'MD'],
            ['parameter_name' => 'Grade', 'parameter_option' => 'Heavy Duty',       'abbreviation' => 'HD'],
            ['parameter_name' => 'Grade', 'parameter_option' => 'Extra Heavy Duty', 'abbreviation' => 'EHD'],


        ];

        foreach ($options as $entry) {
            $parameter = Parameter::where('name', $entry['parameter_name'])->first();

            if ($parameter) {
                ParameterOptionConfig::create([
                    'prod_parameter_id' => $parameter->id,
                    'parameter_option'  => $entry['parameter_option'],
                    'abbreviation'      => $entry['abbreviation'],
                    'modified_by'       => null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
        }
    }
}
