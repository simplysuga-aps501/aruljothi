<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin']); 
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'euser']);
    }
}


