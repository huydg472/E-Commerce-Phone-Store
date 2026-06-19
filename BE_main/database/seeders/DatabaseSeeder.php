<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('TRUNCATE TABLE role_permissions, users, roles RESTART IDENTITY CASCADE');

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            NewsSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductSpecificationSeeder::class,
            ShippingAddressSeeder::class,
            CartSeeder::class,
            OrderSeeder::class,
            AccessorySeeder::class,
        ]);
    }
}
