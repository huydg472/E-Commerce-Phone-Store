<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    public function definition(): array
    {
        $usedUserIds = Cart::query()->pluck('user_id');

        $user = User::query()
            ->whereNotIn('id', $usedUserIds)
            ->inRandomOrder()
            ->first()
            ?? User::factory()->create();

        return [
            'user_id' => $user->id,
            'status' => 'active',
        ];
    }
}
