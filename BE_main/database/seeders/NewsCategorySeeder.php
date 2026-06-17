<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Tin công nghệ', 'Tin mới về công nghệ, AI và thiết bị di động.'],
            ['Đánh giá', 'Đánh giá chi tiết các mẫu điện thoại và phụ kiện.'],
            ['Mẹo hay', 'Thủ thuật sử dụng máy, bảo quản và tối ưu trải nghiệm.'],
            ['Khuyến mãi', 'Ưu đãi, deal hot và chương trình giảm giá.'],
            ['So sánh', 'So sánh các sản phẩm để chọn lựa dễ hơn.'],
            ['Xu hướng', 'Xu hướng thị trường và thị hiếu người dùng.'],
        ];

        foreach ($categories as $index => [$name, $description]) {
            NewsCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
