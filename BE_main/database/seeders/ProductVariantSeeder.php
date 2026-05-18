<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variantsByProduct = [
            'Samsung Galaxy A36 5G' => [
                ['color' => 'Đen', 'storage' => '128GB', 'ram' => '8GB', 'sku' => 'SS-A36-5G-DEN-128-8', 'import_price' => 6200000, 'price' => 7990000, 'sale_price' => 7490000, 'quantity' => 25],
                ['color' => 'Xanh', 'storage' => '256GB', 'ram' => '8GB', 'sku' => 'SS-A36-5G-XANH-256-8', 'import_price' => 7100000, 'price' => 8990000, 'sale_price' => 8490000, 'quantity' => 18],
            ],
            'Samsung Galaxy A26 5G' => [
                ['color' => 'Đen', 'storage' => '128GB', 'ram' => '6GB', 'sku' => 'SS-A26-5G-DEN-128-6', 'import_price' => 5000000, 'price' => 6490000, 'sale_price' => 5990000, 'quantity' => 30],
                ['color' => 'Trắng', 'storage' => '128GB', 'ram' => '8GB', 'sku' => 'SS-A26-5G-TRANG-128-8', 'import_price' => 5400000, 'price' => 6990000, 'sale_price' => null, 'quantity' => 12],
            ],
            'iPhone 15' => [
                ['color' => 'Đen', 'storage' => '128GB', 'ram' => '6GB', 'sku' => 'IP15-DEN-128-6', 'import_price' => 15500000, 'price' => 19990000, 'sale_price' => 18490000, 'quantity' => 15],
                ['color' => 'Hồng', 'storage' => '256GB', 'ram' => '6GB', 'sku' => 'IP15-HONG-256-6', 'import_price' => 18000000, 'price' => 22990000, 'sale_price' => null, 'quantity' => 10],
            ],
            'Xiaomi Redmi Note 13' => [
                ['color' => 'Xanh', 'storage' => '128GB', 'ram' => '6GB', 'sku' => 'XM-RN13-XANH-128-6', 'import_price' => 3200000, 'price' => 4490000, 'sale_price' => 4190000, 'quantity' => 40],
                ['color' => 'Đen', 'storage' => '256GB', 'ram' => '8GB', 'sku' => 'XM-RN13-DEN-256-8', 'import_price' => 4200000, 'price' => 5490000, 'sale_price' => 5190000, 'quantity' => 28],
            ],
        ];

        foreach ($variantsByProduct as $productName => $variants) {
            $product = Product::where('name', $productName)->firstOrFail();

            foreach ($variants as $variantData) {
                ProductVariant::updateOrCreate(
                    ['sku' => $variantData['sku']],
                    [
                        'product_id' => $product->id,
                        'color' => $variantData['color'],
                        'storage' => $variantData['storage'],
                        'ram' => $variantData['ram'],
                        'sku' => $variantData['sku'],
                        'import_price' => $variantData['import_price'],
                        'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'],
                        'quantity' => $variantData['quantity'],
                        'status' => $variantData['quantity'] > 0 ? 'active' : 'out_of_stock',
                        'description' => $productName . ' phiên bản ' . $variantData['color'] . ' ' . $variantData['storage'] . ' ' . $variantData['ram'],
                    ]
                );
            }
        }
    }
}
