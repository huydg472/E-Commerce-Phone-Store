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

        $productsByBrand = [
            'Samsung' => [
                'Samsung Galaxy S24 Ultra', 'Samsung Galaxy S24 Plus', 'Samsung Galaxy S24',
                'Samsung Galaxy S24 FE', 'Samsung Galaxy S23 Ultra', 'Samsung Galaxy S23 Plus',
                'Samsung Galaxy S23', 'Samsung Galaxy S23 FE', 'Samsung Galaxy Z Fold6',
                'Samsung Galaxy Z Flip6', 'Samsung Galaxy Z Fold5', 'Samsung Galaxy Z Flip5',
                'Samsung Galaxy A55 5G', 'Samsung Galaxy A54 5G', 'Samsung Galaxy A36 5G',
                'Samsung Galaxy A35 5G', 'Samsung Galaxy A34 5G', 'Samsung Galaxy A26 5G',
                'Samsung Galaxy A25 5G', 'Samsung Galaxy A24', 'Samsung Galaxy A16 5G',
                'Samsung Galaxy A15 LTE', 'Samsung Galaxy M55 5G', 'Samsung Galaxy M35 5G',
                'Samsung Galaxy M15 5G', 'Samsung Galaxy F55 5G', 'Samsung Galaxy F34 5G',
                'Samsung Galaxy XCover7', 'Samsung Galaxy S22 Ultra', 'Samsung Galaxy S22',
            ],
            'Apple' => [
                'iPhone 16 Pro Max', 'iPhone 16 Pro', 'iPhone 16 Plus', 'iPhone 16',
                'iPhone 15 Pro Max', 'iPhone 15 Pro', 'iPhone 15 Plus', 'iPhone 15',
                'iPhone 14 Pro Max', 'iPhone 14 Pro', 'iPhone 14 Plus', 'iPhone 14',
                'iPhone 13 Pro Max', 'iPhone 13 Pro', 'iPhone 13', 'iPhone 13 mini',
                'iPhone 12 Pro Max', 'iPhone 12 Pro', 'iPhone 12', 'iPhone 12 mini',
                'iPhone 11 Pro Max', 'iPhone 11 Pro', 'iPhone 11', 'iPhone SE 2022',
                'iPhone XR', 'iPhone XS Max', 'iPhone XS', 'iPhone X', 'iPhone 8 Plus',
                'iPhone 8',
            ],
            'Xiaomi' => [
                'Xiaomi 14 Ultra', 'Xiaomi 14', 'Xiaomi 14T Pro', 'Xiaomi 14T',
                'Xiaomi 13T Pro', 'Xiaomi 13T', 'Xiaomi 13 Lite', 'Xiaomi 12T Pro',
                'Xiaomi Redmi Note 13 Pro Plus 5G', 'Xiaomi Redmi Note 13 Pro 5G',
                'Xiaomi Redmi Note 13 Pro', 'Xiaomi Redmi Note 13 5G',
                'Xiaomi Redmi Note 13', 'Xiaomi Redmi Note 12 Pro 5G',
                'Xiaomi Redmi Note 12', 'Xiaomi Redmi 13C', 'Xiaomi Redmi 12',
                'Xiaomi Redmi A3', 'Xiaomi Redmi A2 Plus', 'POCO F6 Pro', 'POCO F6',
                'POCO X6 Pro 5G', 'POCO X6 5G', 'POCO M6 Pro', 'POCO M6',
                'POCO C65', 'POCO C61', 'Xiaomi 11 Lite 5G NE', 'Xiaomi Mi 11',
                'Xiaomi Mi 10T Pro',
            ],
            'OPPO' => [
                'OPPO Find X8 Pro', 'OPPO Find X8', 'OPPO Find X7 Ultra', 'OPPO Find X7',
                'OPPO Find N3', 'OPPO Find N3 Flip', 'OPPO Reno12 Pro 5G',
                'OPPO Reno12 5G', 'OPPO Reno12 F 5G', 'OPPO Reno11 Pro 5G',
                'OPPO Reno11 5G', 'OPPO Reno11 F 5G', 'OPPO Reno10 Pro Plus 5G',
                'OPPO Reno10 Pro 5G', 'OPPO Reno10 5G', 'OPPO Reno8 T 5G',
                'OPPO A98 5G', 'OPPO A79 5G', 'OPPO A78', 'OPPO A77s',
                'OPPO A60', 'OPPO A58', 'OPPO A57', 'OPPO A38', 'OPPO A18',
                'OPPO A17k', 'OPPO F25 Pro 5G', 'OPPO F23 5G', 'OPPO K12x 5G',
                'OPPO K11x',
            ],
            'Vivo' => [
                'Vivo X100 Pro', 'Vivo X100', 'Vivo X90 Pro', 'Vivo X90',
                'Vivo V40 5G', 'Vivo V40 Lite 5G', 'Vivo V30 5G', 'Vivo V30e',
                'Vivo V29 5G', 'Vivo V29e', 'Vivo V27 5G', 'Vivo V25 Pro',
                'Vivo Y100 5G', 'Vivo Y58 5G', 'Vivo Y38 5G', 'Vivo Y36',
                'Vivo Y28', 'Vivo Y27', 'Vivo Y22s', 'Vivo Y21s',
                'Vivo Y18', 'Vivo Y17s', 'Vivo Y16', 'Vivo Y03',
                'Vivo T3 5G', 'Vivo T2 5G', 'Vivo T1 5G', 'Vivo S18 Pro',
                'Vivo S18', 'Vivo iQOO Z9',
            ],
            'Realme' => [
                'Realme GT 6', 'Realme GT 6T', 'Realme GT Neo 6', 'Realme GT Neo 5',
                'Realme 13 Pro Plus 5G', 'Realme 13 Pro 5G', 'Realme 12 Pro Plus 5G',
                'Realme 12 Pro 5G', 'Realme 12 Plus 5G', 'Realme 12 5G',
                'Realme 11 Pro Plus 5G', 'Realme 11 Pro 5G', 'Realme 11',
                'Realme 10 Pro Plus', 'Realme 10 Pro', 'Realme Narzo 70 Pro 5G',
                'Realme Narzo 70 5G', 'Realme Narzo 60x 5G', 'Realme Narzo N65 5G',
                'Realme Narzo N55', 'Realme C67', 'Realme C65', 'Realme C63',
                'Realme C61', 'Realme C55', 'Realme C53', 'Realme C51',
                'Realme Note 50', 'Realme 9 Pro Plus', 'Realme 8 Pro',
            ],
        ];

        foreach ($productsByBrand as $brandName => $productNames) {
            $brand = Brand::where('name', $brandName)->firstOrFail();

            foreach ($productNames as $index => $productName) {
                $slug = Str::slug($productName);

                Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'brand_id' => $brand->id,
                        'category_id' => $phoneCategory->id,
                        'name' => $productName,
                        'slug' => $slug,
                        'thumbnail_url' => null,
                        'short_description' => $productName . ' chinh hang, bao hanh theo chinh sach cua hang.',
                        'description' => $productName . ' phu hop nhu cau su dung hang ngay, giai tri, chup anh va lam viec di dong.',
                        'is_featured' => $index < 5,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
