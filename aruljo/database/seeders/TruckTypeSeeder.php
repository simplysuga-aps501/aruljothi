<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Map existing capacities to new names
        $updates = [
            3000  => 'Mini Door',
            6000  => '6 Wheel Eicher',
            10000 => '6 Wheel',
            12000 => 'Bharath Benz',
            17000 => '10 Wheel Taurus',
            23000 => '12 Wheel',
        ];

        foreach ($updates as $capacity => $name) {
            DB::table('tp_truck_types')->where('capacity_kg', $capacity)->update([
                'name'       => $name,
                'updated_at' => now(),
            ]);
        }

        // Insert new truck types (only if they don't exist)
        $newTrucks = [
            ['capacity_kg' => 25000, 'name' => '14 Wheel'],
            ['capacity_kg' => 25000, 'name' => 'Trailor'],
            ['capacity_kg' => 27000, 'name' => '16 Wheel'],
        ];

        foreach ($newTrucks as $truck) {
            DB::table('tp_truck_types')->updateOrInsert(
                ['capacity_kg' => $truck['capacity_kg'], 'name' => $truck['name']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
