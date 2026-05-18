<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Điện thoại', 'description' => 'Các dòng điện thoại thông minh'],
            ['name' => 'Phụ kiện', 'description' => 'Ốp lưng, sạc, cáp và phụ kiện điện thoại'],
            ['name' => 'Máy tính bảng', 'description' => 'Các dòng tablet phổ biến'],
            ['name' => 'Đồng hồ thông minh', 'description' => 'Smartwatch và vòng đeo tay thông minh'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
