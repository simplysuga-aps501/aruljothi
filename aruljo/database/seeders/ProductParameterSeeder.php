<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameter;

class ProductParameterSeeder extends Seeder
{
   public function run(): void
       {
           $now = Carbon::now();

           $parameters = [
               ['name' => 'Diameter', 'description' => 'dia', 'input_type' => 'number'],
               ['name' => 'Length', 'description' => 'lt', 'input_type' => 'number'],
               ['name' => 'Height', 'description' => 'ht', 'input_type' => 'number'],
               ['name' => 'Thickness', 'description' => 'th', 'input_type' => 'number'],
               ['name' => 'Width', 'description' => 'wd', 'input_type' => 'number'],
               ['name' => 'KV', 'description' => 'kv', 'input_type' => 'number'],
               ['name' => 'Capacity', 'input_type' => 'number'],
               ['name' => 'Shape', 'input_type' => 'select'],
               ['name' => 'Cover', 'input_type' => 'select'],
               ['name' => 'Class', 'input_type' => 'select'],
               ['name' => 'Pipe Type', 'input_type' => 'select'],
           ];

           foreach ($parameters as $param) {
               ProductParameter::create([
                   'name' => $param['name'],
                   'description' => $param['description'] ?? null,
                   'input_type' => $param['input_type'],
                   'modified_by' => null,
                   'created_at' => $now,
                   'updated_at' => $now,
               ]);
           }
       }
}
