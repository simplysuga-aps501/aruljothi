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
       $this->call(RoleSeeder::class);
       $this->call(TemplateSeeder::class);
       $this->call(ParameterSeeder::class);
       $this->call(ParameterUnitsSeeder::class);
       $this->call(ParameterOptionConfigSeeder::class);
       $this->call(ParameterConfigSeeder::class);
       $this->call(ParameterOptionDependenciesSeeder::class);
       $this->call(TruckTypeSeeder::class);
       $this->call(TpMinKmMultiplierSeeder::class);

       // Run the CSV import command
       \Artisan::call('import:distance-pincodes', [
           'file' => storage_path('app/pincodes.csv')
       ]);

       // Optionally show output in console
       $this->command->info('Distance pincodes imported successfully.');
    }
}
