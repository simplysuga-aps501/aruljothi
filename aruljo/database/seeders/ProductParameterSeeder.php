<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameter;

class ProductParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
        {
            $now = Carbon::now();

            $parameters = [
                'Diameter',
                'Length',
                'Height',
                'Thickness',
                'Width',
                'KV',
                'Capacity',
                'Shape',
                'Cover',
                'Class',
                'Pipe Type',
            ];

            foreach ($parameters as $name) {
                ProductParameter::create([
                    'name' => $name,
                    'modified_by' => null, // Replace with user_id if needed
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
}
