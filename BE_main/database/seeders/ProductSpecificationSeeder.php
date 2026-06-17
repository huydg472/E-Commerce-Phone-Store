<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Database\Seeder;

class ProductSpecificationSeeder extends Seeder
{
    public function run(): void
    {
        $specsByProduct = [
            'Samsung Galaxy A36 5G' => [
                'Màn hình' => 'Super AMOLED 6.7 inch, 120Hz',
                'Chip' => 'Exynos tầm trung hỗ trợ 5G',
                'Camera sau' => '50MP + 8MP + 5MP',
                'Pin' => '5000mAh',
                'Sạc' => '25W',
            ],
            'Samsung Galaxy A26 5G' => [
                'Màn hình' => 'Super AMOLED 6.7 inch',
                'Chip' => 'Chip 5G tiết kiệm điện',
                'Camera sau' => '50MP',
                'Pin' => '5000mAh',
                'Sạc' => '25W',
            ],
            'iPhone 15' => [
                'Màn hình' => 'Super Retina XDR 6.1 inch',
                'Chip' => 'Apple A16 Bionic',
                'Camera sau' => '48MP',
                'Pin' => 'Dùng cả ngày',
                'Cổng sạc' => 'USB-C',
            ],
            'Xiaomi Redmi Note 13' => [
                'Màn hình' => 'AMOLED 6.67 inch, 120Hz',
                'Chip' => 'Snapdragon tầm trung',
                'Camera sau' => '108MP',
                'Pin' => '5000mAh',
                'Sạc' => '33W',
            ],
        ];

        foreach ($specsByProduct as $productName => $specs) {
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                continue;
            }

            $sortOrder = 0;

            foreach ($specs as $name => $value) {
                ProductSpecification::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'spec_name' => $name,
                    ],
                    [
                        'product_id' => $product->id,
                        'spec_name' => $name,
                        'spec_value' => $value,
                        'sort_order' => $sortOrder++,
                    ]
                );
            }
        }
    }
}
