<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Get all roles from Spatie roles table
        $roles = Role::pluck('name')->toArray();

        foreach ($roles as $roleName) {
            $email = strtolower($roleName) . '@example.com';
            $password = strtolower($roleName) . '@123';

            // Create user if not exists
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => ucfirst($roleName),
                    'password' => Hash::make($password),
                ]
            );

            // Assign the role (ensure it’s not duplicated)
            if (!$user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            // Optional: log info in console
            $this->command->info("User created: {$email} (Role: {$roleName}, Password: {$password})");
        }
    }
}
