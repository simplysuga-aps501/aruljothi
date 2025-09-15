<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call(TemplateSeeder::class);
       $this->call(ParameterSeeder::class);
       $this->call(ParameterOptionConfigSeeder::class);
       $this->call(ParameterConfigSeeder::class);
       $this->call(ParameterOptionDependenciesSeeder::class);
       $this->call(TruckTypeSeeder::class);
    }
}
