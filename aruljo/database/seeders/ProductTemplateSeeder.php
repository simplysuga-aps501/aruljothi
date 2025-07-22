<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\ProductTemplate;

class ProductTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $data = [
                   ['name' => 'pipe',        'modified_by' => 1],
                   ['name' => 'offcut',      'modified_by' => 1],
                   ['name' => 'chamber',     'modified_by' => 1],
                   ['name' => 'ring',        'modified_by' => 1],
                   ['name' => 'vtrough',     'modified_by' => 1],
                   ['name' => 'water tank',  'modified_by' => 1],
                   ['name' => 'cover',       'modified_by' => 1],
               ];

               foreach ($data as $item) {
                   ProductTemplate::create($item);
                   }
    }
}
