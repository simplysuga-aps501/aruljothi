<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\Parameter;

class ParameterSeeder extends Seeder
{
   public function run(): void
       {
           $now = Carbon::now();

           $parameters = [
               ['name' => 'Shape', 'input_type' => 'select'],
               ['name' => 'Diameter', 'description' => 'Diameter', 'input_type' => 'number'],
               ['name' => 'Thickness', 'description' => 'Thickness', 'input_type' => 'number'],
               ['name' => 'Length', 'description' => 'Length', 'input_type' => 'number'],
               ['name' => 'Width', 'description' => 'Width', 'input_type' => 'number'],
               ['name' => 'Height', 'description' => 'Height', 'input_type' => 'number'],
               ['name' => 'KV', 'description' => 'kv', 'input_type' => 'number'],
               ['name' => 'Capacity', 'input_type' => 'number'],
               ['name' => 'Cover', 'input_type' => 'select'],
               ['name' => 'Class', 'description' => 'Class','input_type' => 'select'],
               ['name' => 'Pipe Type', 'description' => 'Type','input_type' => 'select'],
               ['name' => 'Handle', 'input_type' => 'select'],
               ['name' => 'Partition', 'input_type' => 'select'],
           ];

           foreach ($parameters as $param) {
               Parameter::create([
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
