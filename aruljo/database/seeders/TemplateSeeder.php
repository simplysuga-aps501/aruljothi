<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Template;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'rcc pipe',        'abbreviation' => 'RP', 'modified_by' => 1],
            ['name' => 'offcut rcc pipe', 'abbreviation' => 'ORP',  'modified_by' => 1],
            ['name' => 'chamber',         'abbreviation' => 'CHB',  'modified_by' => 1],
            ['name' => 'ring',            'abbreviation' => 'RNG',  'modified_by' => 1],
            ['name' => 'v trough',        'abbreviation' => 'VTR',  'modified_by' => 1],
            ['name' => 'water tank',      'abbreviation' => 'WTK',  'modified_by' => 1],
            ['name' => 'cover',           'abbreviation' => 'CVR',  'modified_by' => 1],
            ['name' => 'manhole cover',   'abbreviation' => 'MHC',  'modified_by' => 1],
        ];

        foreach ($data as $item) {
            Template::create($item);
        }
    }
}
