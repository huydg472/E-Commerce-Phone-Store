<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductVariantImageSeeder::class,
            ProductSpecificationSeeder::class,
            ShippingAddressSeeder::class,
            CartSeeder::class,
            CartItemSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            PaymentSeeder::class,
            StockLogSeeder::class,
        ]);
    }
}
