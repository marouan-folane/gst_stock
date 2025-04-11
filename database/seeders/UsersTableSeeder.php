<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // Create manager user
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => 'password',
            'role' => 'manager',
        ]);

        // Create employee user
        User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => 'password',
            'role' => 'employee',
        ]);
    }
}
