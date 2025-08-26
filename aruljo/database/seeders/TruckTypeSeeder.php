<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $truckTypes = [
            ['name' => '3 Ton',  'capacity_kg' => 3000,  'description' => ''],
            ['name' => '6 Ton',  'capacity_kg' => 6000,  'description' => ''],
            ['name' => '10 Ton', 'capacity_kg' => 10000, 'description' => ''],
            ['name' => '12 Ton', 'capacity_kg' => 12000, 'description' => ''],
            ['name' => '17 Ton', 'capacity_kg' => 17000, 'description' => ''],
            ['name' => '23 Ton', 'capacity_kg' => 23000, 'description' => ''],
        ];

        DB::table('tp_truck_types')->insert($truckTypes);
    }
}
