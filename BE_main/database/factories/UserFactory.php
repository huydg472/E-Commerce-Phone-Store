<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $roleId = Role::where('name', 'customer')->value('id')
            ?? Role::query()->value('id')
            ?? Role::create([
                'name' => 'customer',
                'display_name' => 'Khách hàng',
                'description' => 'Người dùng mua hàng trên website',
                'status' => 'active',
            ])->id;

        return [
            'role_id' => $roleId,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '09' . fake()->unique()->numerify('########'),
            'username' => fake()->unique()->userName(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }
}
