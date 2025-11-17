<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckTypeSeeder extends Seeder
{
    public function run(): void
    {
        $updates = [
            3000  => ['name' => 'Mini Door', 'rate_per_km' => 38],
            6000  => ['name' => '6 Wheel Eicher', 'rate_per_km' => 56],
            10000 => ['name' => '6 Wheel', 'rate_per_km' => 80],
            12000 => ['name' => 'Bharath Benz', 'rate_per_km' => 90],
            17000 => ['name' => '10 Wheel Taurus', 'rate_per_km' => 100],
            23000 => ['name' => '12 Wheel', 'rate_per_km' => 110],
        ];

        foreach ($updates as $capacity => $data) {
            DB::table('tp_truck_types')->updateOrInsert(
                ['capacity_kg' => $capacity],
                [
                    'name'        => $data['name'],
                    'rate_per_km' => $data['rate_per_km'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        $newTrucks = [
            ['capacity_kg' => 25000, 'name' => '14 Wheel', 'rate_per_km' => 125],
            ['capacity_kg' => 25000, 'name' => 'Trailor', 'rate_per_km' => 140],
            ['capacity_kg' => 27000, 'name' => '16 Wheel', 'rate_per_km' => 150],
        ];

        foreach ($newTrucks as $truck) {
            DB::table('tp_truck_types')->updateOrInsert(
                ['capacity_kg' => $truck['capacity_kg'], 'name' => $truck['name']],
                [
                    'rate_per_km' => $truck['rate_per_km'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }
}