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
       $this->call(ProductTemplateSeeder::class);
       $this->call(ProductParameterSeeder::class);
       $this->call(ProductParameterUnitSeeder::class);
       $this->call(ProductParameterUnitConfigSeeder::class);
       $this->call(ProductParameterOptionConfigSeeder::class);
       $this->call(ProductParameterConfigSeeder::class);
    }
}
