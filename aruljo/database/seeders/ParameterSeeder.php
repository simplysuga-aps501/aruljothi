<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;

class ParameterSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $parameters = [
            ['name' => 'Shape',      'description' => null,                     'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Voltage',   'description' => 'Voltage',               'input_type' => 'number', 'abbreviation' => 'KV',  'unit' => 'KV'],
            ['name' => 'Diameter',   'description' => 'Diameter',               'input_type' => 'number', 'abbreviation' => null,  'unit' => 'MM'],
            ['name' => 'Length',     'description' => 'Length',                 'input_type' => 'number', 'abbreviation' => null,  'unit' => 'MTR'],
            ['name' => 'Size',       'description' => 'Size',                   'input_type' => 'number', 'abbreviation' => null,  'unit' => 'MM'],
            ['name' => 'Thickness',  'description' => 'Thickness',              'input_type' => 'number', 'abbreviation' => null,  'unit' => 'MM'],
            ['name' => 'Height',     'description' => 'Height',                 'input_type' => 'number', 'abbreviation' => null,  'unit' => 'MM'],
            ['name' => 'Breadth',     'description' => 'Breadth',                 'input_type' => 'number', 'abbreviation' => null,  'unit' => 'MM'],
            ['name' => 'Capacity',   'description' => 'capacity',               'input_type' => 'number', 'abbreviation' => 'LTR', 'unit' => 'LTR'],
            ['name' => 'Cover',      'description' => null,                     'input_type' => 'select', 'abbreviation' => 'CVR', 'unit' => null],
            ['name' => 'Class',      'description' => 'Class',                  'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Pipe Type',  'description' => 'Type',                   'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Pillar Type',  'description' => 'Type',                   'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Stone Type',  'description' => 'Type',                   'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Handle',     'description' => null,                     'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Partition',  'description' => null,                     'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Holes',  'description' => null,                     'input_type' => 'select', 'abbreviation' => null,  'unit' => null],
            ['name' => 'Grade',   'description' => null,                        'input_type' => 'select', 'abbreviation' => null,  'unit' => null],

        ];

        foreach ($parameters as $param) {
            Parameter::updateOrCreate(
                ['name' => $param['name']], // unique key
                [
                    'description'  => $param['description'] ?? null,
                    'input_type'   => $param['input_type'],
                    'abbreviation' => $param['abbreviation'] ?? null,
                    'unit'         => $param['unit'] ?? null,
                    'modified_by'  => null,
                    'updated_at'   => $now,
                    'created_at'   => $now,
                ]
            );

        }
    }
}
