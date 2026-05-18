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
            ['name' => 'Samsung', 'description' => 'Thương hiệu điện thoại đến từ Hàn Quốc'],
            ['name' => 'Apple', 'description' => 'Thương hiệu iPhone và thiết bị thông minh'],
            ['name' => 'Xiaomi', 'description' => 'Thương hiệu điện thoại hiệu năng tốt trong tầm giá'],
            ['name' => 'OPPO', 'description' => 'Thương hiệu nổi bật về thiết kế và camera'],
            ['name' => 'Vivo', 'description' => 'Thương hiệu điện thoại phổ biến tại Việt Nam'],
            ['name' => 'Realme', 'description' => 'Thương hiệu điện thoại trẻ trung, giá tốt'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                [
                    'name' => $brand['name'],
                    'slug' => Str::slug($brand['name']),
                    'logo_url' => 'https://placehold.co/300x300?text=' . rawurlencode($brand['name']),
                    'description' => $brand['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
