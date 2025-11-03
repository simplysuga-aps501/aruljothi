<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TpMinKmMultiplierSeeder extends Seeder
{
    public function run(): void
    {
        $slabs = [
            ['min_km' => 0,  'max_km' => 15,  'multiplier' => 2.5],
            ['min_km' => 15, 'max_km' => 30,  'multiplier' => 2.0],
            ['min_km' => 30, 'max_km' => 45,  'multiplier' => 1.75],
            ['min_km' => 45, 'max_km' => 60,  'multiplier' => 1.5],
            ['min_km' => 60, 'max_km' => 80,  'multiplier' => 1.25],
            ['min_km' => 80, 'max_km' => null,'multiplier' => 1.0],
        ];

        foreach ($slabs as $slab) {
            DB::table('tp_min_km_multipliers')->updateOrInsert(
                ['min_km' => $slab['min_km'], 'max_km' => $slab['max_km']],
                ['multiplier' => $slab['multiplier'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
