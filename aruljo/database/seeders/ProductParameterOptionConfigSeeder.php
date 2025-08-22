<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ProductParameterOptionConfig;
use App\Models\Product\ProductParameter;
use Carbon\Carbon;

class ProductParameterOptionConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::parse('2025-07-22 10:00:00');

        // Fetch all parameter IDs by name
        $paramIds = ProductParameter::pluck('id', 'name')->toArray();


        $options = [
            // Shape options
            [
                'product_parameter_id' => $paramIds['Shape'],
                'parameter_option'     => 'Round',
                'dependencies'         => json_encode(['requires' => [
                    $paramIds['Diameter'],
                    $paramIds['Thickness'],
                ]])
            ],
            [
                'product_parameter_id' => $paramIds['Shape'],
                'parameter_option'     => 'Square',
                'dependencies'         => json_encode(['requires' => [
                    $paramIds['Length'],
                    $paramIds['Width'],
                    $paramIds['Height'],
                ]])
            ],

            // Cover options
            [
                'product_parameter_id' => $paramIds['Cover'],
                'parameter_option'     => 'With Lid',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Cover'],
                'parameter_option'     => 'Without Lid',
                'dependencies'         => null
            ],

            // Handle options
            [
                'product_parameter_id' => $paramIds['Handle'],
                'parameter_option'     => 'With Handle',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Handle'],
                'parameter_option'     => 'Without Handle',
                'dependencies'         => null
            ],

            // Partition options
            [
                'product_parameter_id' => $paramIds['Partition'],
                'parameter_option'     => 'Single Piece',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Partition'],
                'parameter_option'     => 'Two Halves',
                'dependencies'         => null
            ],

            // NP Class options
            [
                'product_parameter_id' => $paramIds['Class'],
                'parameter_option'     => 'NP2',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Class'],
                'parameter_option'     => 'NP3',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Class'],
                'parameter_option'     => 'NP4',
                'dependencies'         => null
            ],

            // Pipe type options
            [
                'product_parameter_id' => $paramIds['Pipe Type'],
                'parameter_option'     => 'Plain',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Pipe Type'],
                'parameter_option'     => 'Spygot',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Pipe Type'],
                'parameter_option'     => 'Male Female',
                'dependencies'         => null
            ],
            [
                'product_parameter_id' => $paramIds['Pipe Type'],
                'parameter_option'     => 'Plain End With Separate Collar',
                'dependencies'         => null
            ],
        ];

        foreach ($options as $entry) {
            ProductParameterOptionConfig::create([
                'product_parameter_id' => $entry['product_parameter_id'],
                'parameter_option'     => $entry['parameter_option'],
                'dependencies'         => $entry['dependencies'],
                'modified_by'          => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
    }
}
