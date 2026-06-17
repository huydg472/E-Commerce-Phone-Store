<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $customerRoleId = Role::where('name', 'customer')->value('id');

        User::query()
            ->when($customerRoleId, fn($query) => $query->where('role_id', $customerRoleId))
            ->limit(5)
            ->get()
            ->each(function (User $user) {
                Cart::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'status' => 'active',
                    ]
                );
            });
    }
}
