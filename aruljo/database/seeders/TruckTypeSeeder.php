<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckTypeSeeder extends Seeder
{
    public function run(): void
    {
        $trucks = [
            ['capacity_kg' => 3000,  'name' => 'Mini Door',       'rate_per_km' => 38,  'unloading_charges_below_150' => 700,  'unloading_charges_above_150' => 0],
            ['capacity_kg' => 6000,  'name' => '6 Wheel Eicher',  'rate_per_km' => 56,  'unloading_charges_below_150' => 1000, 'unloading_charges_above_150' => 2500],
            ['capacity_kg' => 10000, 'name' => '6 Wheel',         'rate_per_km' => 80,  'unloading_charges_below_150' => 1500, 'unloading_charges_above_150' => 3000],
            ['capacity_kg' => 12000, 'name' => 'Bharath Benz',    'rate_per_km' => 90,  'unloading_charges_below_150' => 1800, 'unloading_charges_above_150' => 3000],
            ['capacity_kg' => 17000, 'name' => '10 Wheel Taurus', 'rate_per_km' => 100, 'unloading_charges_below_150' => 2500, 'unloading_charges_above_150' => 4000],
            ['capacity_kg' => 23000, 'name' => '12 Wheel',        'rate_per_km' => 110, 'unloading_charges_below_150' => 3000, 'unloading_charges_above_150' => 4500],
            ['capacity_kg' => 25000, 'name' => '14 Wheel',        'rate_per_km' => 125, 'unloading_charges_below_150' => 3500, 'unloading_charges_above_150' => 4500],
            ['capacity_kg' => 25000, 'name' => 'Trailor',         'rate_per_km' => 140, 'unloading_charges_below_150' => 0,    'unloading_charges_above_150' => 0],
            ['capacity_kg' => 27000, 'name' => '16 Wheel',        'rate_per_km' => 150, 'unloading_charges_below_150' => 4000, 'unloading_charges_above_150' => 5000],
        ];

        foreach ($trucks as $truck) {
            DB::table('tp_truck_types')->updateOrInsert(
                ['capacity_kg' => $truck['capacity_kg'], 'name' => $truck['name']],
                [
                    'rate_per_km' => $truck['rate_per_km'],
                    'unloading_charges_below_150' => $truck['unloading_charges_below_150'],
                    'unloading_charges_above_150' => $truck['unloading_charges_above_150'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }
}
