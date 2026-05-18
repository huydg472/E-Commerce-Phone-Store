<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'phone' => '0900000001',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'staff'],
            [
                'role_id' => $staffRole->id,
                'name' => 'Staff',
                'email' => 'staff@example.com',
                'phone' => '0900000003',
                'username' => 'staff',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'customer'],
            [
                'role_id' => $customerRole->id,
                'name' => 'Customer',
                'email' => 'customer@example.com',
                'phone' => '0900000002',
                'username' => 'customer',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        User::factory()->count(8)->create([
            'role_id' => $customerRole->id,
        ]);
    }
}
