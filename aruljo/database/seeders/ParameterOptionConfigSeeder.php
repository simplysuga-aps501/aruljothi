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

            // Shape - Cover
            ['parameter_name' => 'Shape(Cover)', 'parameter_option' => 'Round(C)',  'abbreviation' => 'RND'],
            ['parameter_name' => 'Shape(Cover)', 'parameter_option' => 'Square(C)', 'abbreviation' => 'SQR'],

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
            ['parameter_name' => 'Handle', 'parameter_option' => 'Adjustable Handle', 'abbreviation' => 'AHDL'],

            // Partition
            ['parameter_name' => 'Partition', 'parameter_option' => 'Two Halves',   'abbreviation' => '2H'],
            ['parameter_name' => 'Partition', 'parameter_option' => 'Single Piece', 'abbreviation' => '1P'],

            // Holes
            ['parameter_name' => 'Holes', 'parameter_option' => 'With Holes',   'abbreviation' => 'HOLE'],
            ['parameter_name' => 'Holes', 'parameter_option' => 'Without Holes', 'abbreviation' => null],

            // Strength for Manhole Cover
            ['parameter_name' => 'Grade', 'parameter_option' => 'Medium Duty',      'abbreviation' => 'MD'],
            ['parameter_name' => 'Grade', 'parameter_option' => 'Heavy Duty',       'abbreviation' => 'HD'],
            ['parameter_name' => 'Grade', 'parameter_option' => 'Extra Heavy Duty', 'abbreviation' => 'EHD'],

            // Cement Pillar Type
            ['parameter_name' => 'Pillar Type', 'parameter_option' => 'Plain End',  'abbreviation' => 'PLN'],
            ['parameter_name' => 'Pillar Type', 'parameter_option' => 'With Rod',    'abbreviation' => 'ROD'],
            ['parameter_name' => 'Pillar Type', 'parameter_option' => 'U Shaped Top','abbreviation' => 'UT'],

            // Kerb Type
            ['parameter_name' => 'Stone Type', 'parameter_option' => 'Onida',  'abbreviation' => 'PLN'],
            ['parameter_name' => 'Stone Type', 'parameter_option' => 'Rectangle',    'abbreviation' => 'ROD'],

        ];

        foreach ($options as $entry) {
            $parameter = Parameter::where('name', $entry['parameter_name'])->first();

            if ($parameter) {
                ParameterOptionConfig::updateOrCreate(
                    [
                        'prod_parameter_id' => $parameter->id,
                        'parameter_option'  => $entry['parameter_option'],
                    ],
                    [
                        'abbreviation' => $entry['abbreviation'],
                        'modified_by'  => 1,
                        'created_at'   => $now,  // will be used only on insert
                        'updated_at'   => $now,  // will be used on insert and update
                    ]
                );

            }
        }
    }
}
