<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $phoneCategory = Category::where('slug', 'dien-thoai')->firstOrFail();

        $products = [
            [
                'brand' => 'Samsung',
                'name' => 'Samsung Galaxy A36 5G',
                'featured' => true,
                'short_description' => 'Điện thoại Samsung tầm trung hỗ trợ 5G, màn hình đẹp, pin tốt.',
                'description' => 'Samsung Galaxy A36 5G phù hợp cho người dùng cần điện thoại ổn định, thiết kế hiện đại và camera đa dụng.',
            ],
            [
                'brand' => 'Samsung',
                'name' => 'Samsung Galaxy A26 5G',
                'featured' => false,
                'short_description' => 'Điện thoại Samsung 5G giá dễ tiếp cận.',
                'description' => 'Samsung Galaxy A26 5G phù hợp nhu cầu học tập, giải trí và sử dụng hằng ngày.',
            ],
            [
                'brand' => 'Apple',
                'name' => 'iPhone 15',
                'featured' => true,
                'short_description' => 'iPhone với Dynamic Island, camera tốt và hiệu năng mạnh.',
                'description' => 'iPhone 15 phù hợp người dùng cần hiệu năng ổn định, chụp ảnh tốt và hệ sinh thái Apple.',
            ],
            [
                'brand' => 'Xiaomi',
                'name' => 'Xiaomi Redmi Note 13',
                'featured' => false,
                'short_description' => 'Điện thoại giá tốt, màn hình đẹp, pin lớn.',
                'description' => 'Xiaomi Redmi Note 13 phù hợp người dùng muốn cấu hình tốt trong tầm giá.',
            ],
        ];

        foreach ($products as $productData) {
            $brand = Brand::where('name', $productData['brand'])->firstOrFail();
            $slug = Str::slug($productData['name']);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'brand_id' => $brand->id,
                    'category_id' => $phoneCategory->id,
                    'name' => $productData['name'],
                    'slug' => $slug,
                    'thumbnail_url' => 'https://placehold.co/600x600?text=' . rawurlencode($productData['name']),
                    'short_description' => $productData['short_description'],
                    'description' => $productData['description'],
                    'is_featured' => $productData['featured'],
                    'status' => 'active',
                ]
            );
        }
    }
}
