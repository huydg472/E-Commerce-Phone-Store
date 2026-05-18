<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Quản trị viên',
                'description' => 'Có toàn quyền quản lý hệ thống',
                'status' => 'active',
            ],
            [
                'name' => 'staff',
                'display_name' => 'Nhân viên',
                'description' => 'Nhân viên quản lý sản phẩm, đơn hàng, thanh toán và tồn kho',
                'status' => 'active',
            ],
            [
                'name' => 'customer',
                'display_name' => 'Khách hàng',
                'description' => 'Người dùng mua hàng trên website',
                'status' => 'active',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
