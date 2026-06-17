<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Samsung', 'type' => 'phone', 'description' => 'Thuong hieu dien thoai den tu Han Quoc'],
            ['name' => 'Apple', 'type' => 'phone', 'description' => 'Thuong hieu iPhone va thiet bi thong minh'],
            ['name' => 'Xiaomi', 'type' => 'phone', 'description' => 'Thuong hieu dien thoai hieu nang tot trong tam gia'],
            ['name' => 'OPPO', 'type' => 'phone', 'description' => 'Thuong hieu noi bat ve thiet ke va camera'],
            ['name' => 'Vivo', 'type' => 'phone', 'description' => 'Thuong hieu dien thoai pho bien tai Viet Nam'],
            ['name' => 'Realme', 'type' => 'phone', 'description' => 'Thuong hieu dien thoai tre trung, gia tot'],
            ['name' => 'Anker', 'type' => 'accessory', 'description' => 'Phu kien sac nhanh, pin du phong va cap ket noi'],
            ['name' => 'UGREEN', 'type' => 'accessory', 'description' => 'Giai phap sac, hub va ket noi phu kien'],
            ['name' => 'Baseus', 'type' => 'accessory', 'description' => 'Phu kien dien thoai va sac noi bat'],
            ['name' => 'Belkin', 'type' => 'accessory', 'description' => 'Phu kien cao cap cho sac va bao ve'],
            ['name' => 'ESR', 'type' => 'accessory', 'description' => 'Phu kien MagSafe, op lung va sac khong day'],
            ['name' => 'Spigen', 'type' => 'accessory', 'description' => 'Op lung, kinh cuong luc va phu kien bao ve'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                [
                    'name' => $brand['name'],
                    'slug' => Str::slug($brand['name']),
                    'type' => $brand['type'],
                    'logo_url' => 'https://placehold.co/300x300?text=' . rawurlencode($brand['name']),
                    'description' => $brand['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
