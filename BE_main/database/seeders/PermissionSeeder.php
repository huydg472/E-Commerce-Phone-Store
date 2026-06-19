<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'roles' => 'Vai trò',
            'users' => 'Người dùng',
            'permissions' => 'Quyền',
            'brands' => 'Thương hiệu',
            'categories' => 'Danh mục',
            'products' => 'Sản phẩm',
            'product_variants' => 'Biến thể sản phẩm',
            'product_variant_images' => 'Ảnh biến thể sản phẩm',
            'product_specifications' => 'Thông số sản phẩm',
            'news_categories' => 'Danh mục tin tức',
            'news_posts' => 'Bài viết tin tức',
            'carts' => 'Giỏ hàng',
            'cart_items' => 'Sản phẩm trong giỏ hàng',
            'shipping_addresses' => 'Địa chỉ giao hàng',
            'orders' => 'Đơn hàng',
            'order_items' => 'Chi tiết đơn hàng',
            'payments' => 'Thanh toán',
            'coupons' => 'Mã giảm giá',
            'stock_logs' => 'Nhật ký kho',
            'settings' => 'Cấu hình hệ thống',
        ];

        $actions = [
            'view' => 'Xem',
            'create' => 'Thêm',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
        ];

        foreach ($modules as $module => $moduleDisplayName) {
            foreach ($actions as $action => $actionDisplayName) {
                Permission::updateOrCreate(
                    [
                        'module' => $module,
                        'action' => $action,
                    ],
                    [
                        'name' => $module . '.' . $action,
                        'display_name' => $actionDisplayName . ' ' . Str::lower($moduleDisplayName),
                        'module' => $module,
                        'action' => $action,
                        'description' => $actionDisplayName . ' dữ liệu ' . Str::lower($moduleDisplayName),
                    ]
                );
            }
        }

        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', 'customer')->first();

        if ($adminRole) {
            Permission::query()->each(function (Permission $permission) use ($adminRole) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
                ], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        }

        if ($staffRole) {
            $staffPermissions = Permission::query()
                ->whereIn('name', [
                    'brands.view',
                    'categories.view',
                    'products.view',
                    'products.create',
                    'products.update',
                    'product_variants.view',
                    'product_variants.create',
                    'product_variants.update',
                    'product_variant_images.view',
                    'product_variant_images.create',
                    'product_variant_images.update',
                    'product_variant_images.delete',
                    'product_specifications.view',
                    'product_specifications.create',
                    'product_specifications.update',
                    'product_specifications.delete',
                    'news_categories.view',
                    'news_categories.create',
                    'news_categories.update',
                    'news_categories.delete',
                    'news_posts.view',
                    'news_posts.create',
                    'news_posts.update',
                    'news_posts.delete',
                    'orders.view',
                    'orders.update',
                    'order_items.view',
                    'shipping_addresses.view',
                    'coupons.view',
                    'coupons.create',
                    'coupons.update',
                    'payments.view',
                    'payments.update',
                    'stock_logs.view',
                    'stock_logs.create',
                    'settings.view',
                    'settings.update',
                ])
                ->get();

            foreach ($staffPermissions as $permission) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id' => $staffRole->id,
                    'permission_id' => $permission->id,
                ], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($customerRole) {
            $customerPermissions = Permission::query()
                ->whereIn('name', [
                    'products.view',
                    'product_variants.view',
                    'product_variant_images.view',
                    'product_specifications.view',
                    'news_categories.view',
                    'news_posts.view',
                    'carts.view',
                    'carts.create',
                    'carts.update',
                    'carts.delete',
                    'cart_items.view',
                    'cart_items.create',
                    'cart_items.update',
                    'cart_items.delete',
                    'orders.view',
                    'orders.create',
                    'order_items.view',
                    'shipping_addresses.view',
                    'shipping_addresses.create',
                    'shipping_addresses.update',
                    'shipping_addresses.delete',
                    'payments.view',
                    'payments.create',
                ])
                ->get();

            foreach ($customerPermissions as $permission) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id' => $customerRole->id,
                    'permission_id' => $permission->id,
                ], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
