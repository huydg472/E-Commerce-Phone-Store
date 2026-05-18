<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingAddressFactory extends Factory
{
    public function definition(): array
    {
        $userId = User::query()->inRandomOrder()->value('id')
            ?? User::factory()->create()->id;

        return [
            'user_id' => $userId,
            'receiver_name' => fake()->name(),
            'receiver_phone' => '09' . fake()->numerify('########'),
            'province' => fake()->randomElement(['Hải Phòng', 'Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng']),
            'district' => fake()->randomElement(['Lê Chân', 'Ngô Quyền', 'Hồng Bàng', 'Cầu Giấy', 'Quận 1']),
            'ward' => fake()->randomElement(['Phường 1', 'Phường 2', 'Phường 3', 'Phường Dư Hàng Kênh']),
            'address_detail' => fake()->streetAddress(),
            'note' => fake()->optional()->sentence(),
            'is_default' => false,
        ];
    }
}
