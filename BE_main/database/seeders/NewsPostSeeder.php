<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsPostSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Tin công nghệ', 'iPhone 16 Pro Max: Những thay đổi đáng chú ý khi lên đời', true, 14],
            ['Tin công nghệ', 'Samsung Galaxy Z Fold7 được kỳ vọng có gì mới?', true, 11],
            ['Đánh giá', 'Đánh giá Xiaomi 15: nhỏ gọn nhưng hiệu năng mạnh', false, 9],
            ['Đánh giá', 'OPPO Reno13 Pro có đáng mua trong tầm giá?', false, 6],
            ['Mẹo hay', '5 mẹo kéo dài tuổi thọ pin cho điện thoại Android', false, 18],
            ['Mẹo hay', 'Cách dọn bộ nhớ điện thoại mà không mất dữ liệu', false, 12],
            ['Khuyến mãi', 'Top ưu đãi phụ kiện trong tuần này tại ZinMobile', true, 8],
            ['Khuyến mãi', 'Mua sạc nhanh tặng kèm cáp: chọn thế nào cho hợp?', false, 5],
            ['So sánh', 'iPhone 16 vs Galaxy S25: chọn máy nào phù hợp hơn?', false, 10],
            ['So sánh', 'So sánh 20W, 30W và 65W: sạc nào hợp với bạn?', false, 7],
            ['Xu hướng', 'Xu hướng điện thoại mỏng nhẹ đang quay trở lại', false, 15],
            ['Xu hướng', 'AI trên smartphone sẽ thay đổi cách chúng ta dùng máy ra sao?', true, 13],
        ];

        foreach ($items as $index => [$categoryName, $title, $isFeatured, $readingMinutes]) {
            $category = NewsCategory::where('name', $categoryName)->first();

            if (!$category) {
                continue;
            }

            $publishedAt = now()->subDays(count($items) - $index);

            NewsPost::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'news_category_id' => $category->id,
                    'title' => $title,
                    'excerpt' => Str::limit($title . ' - cập nhật nội dung chi tiết, ngắn gọn và dễ đọc cho người dùng.', 180),
                    'content' => implode("\n\n", [
                        'Đây là bài viết mẫu cho chuyên mục ' . $category->name . '.',
                        'Nội dung được tạo để phục vụ luồng hiển thị tin tức, danh mục, bài nổi bật và bài liên quan.',
                        'Bạn có thể thay thế nội dung này bằng bài viết thật khi đưa hệ thống vào vận hành.',
                    ]),
                    'featured_image_url' => 'https://placehold.co/1200x700?text=' . rawurlencode($title),
                    'status' => 'published',
                    'is_featured' => $isFeatured,
                    'reading_minutes' => $readingMinutes,
                    'views_count' => 500 + ($index * 137),
                    'published_at' => $publishedAt,
                ]
            );
        }
    }
}
