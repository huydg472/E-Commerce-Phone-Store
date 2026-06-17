<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShippingAddressSeeder extends Seeder
{
    public function run(): void
    {
        $customerRoleId = Role::where('name', 'customer')->value('id');

        User::query()
            ->when($customerRoleId, fn($query) => $query->where('role_id', $customerRoleId))
            ->get()
            ->each(function (User $user) {
                ShippingAddress::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'is_default' => true,
                    ],
                    [
                        'user_id' => $user->id,
                        'receiver_name' => $user->name,
                        'receiver_phone' => $user->phone,
                        'province' => 'Hải Phòng',
                        'district' => 'Lê Chân',
                        'ward' => 'Dư Hàng Kênh',
                        'address_detail' => 'Số 1 đường mẫu',
                        'note' => 'Địa chỉ mặc định',
                        'is_default' => true,
                    ]
                );
            });
    }
}
