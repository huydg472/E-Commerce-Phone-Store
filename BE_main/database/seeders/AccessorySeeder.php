<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccessorySeeder extends Seeder
{
    private function brandCatalog(): array
    {
        return [
            'Anker' => [
                [
                    'line' => 'Nano Charger',
                    'items' => [
                        ['label' => '20W', 'storage' => '20W', 'color' => 'Black', 'price' => 399000],
                        ['label' => '30W', 'storage' => '30W', 'color' => 'White', 'price' => 459000],
                        ['label' => '45W', 'storage' => '45W', 'color' => 'Black', 'price' => 599000],
                        ['label' => '65W', 'storage' => '65W', 'color' => 'White', 'price' => 799000],
                        ['label' => '100W', 'storage' => '100W', 'color' => 'Black', 'price' => 1099000],
                    ],
                ],
                [
                    'line' => 'PowerCore Power Bank',
                    'items' => [
                        ['label' => '5000mAh', 'storage' => '5000mAh', 'color' => 'Black', 'price' => 329000],
                        ['label' => '10000mAh', 'storage' => '10000mAh', 'color' => 'Black', 'price' => 499000],
                        ['label' => '20000mAh', 'storage' => '20000mAh', 'color' => 'Gray', 'price' => 799000],
                        ['label' => '30000mAh', 'storage' => '30000mAh', 'color' => 'Gray', 'price' => 999000],
                        ['label' => 'MagGo 10000mAh', 'storage' => '10000mAh', 'color' => 'White', 'price' => 999000],
                    ],
                ],
                [
                    'line' => 'USB-C Cable',
                    'items' => [
                        ['label' => '0.5m', 'storage' => '0.5m', 'color' => 'Black', 'price' => 99000],
                        ['label' => '1m', 'storage' => '1m', 'color' => 'Black', 'price' => 129000],
                        ['label' => '1.5m', 'storage' => '1.5m', 'color' => 'Black', 'price' => 149000],
                        ['label' => '2m', 'storage' => '2m', 'color' => 'Black', 'price' => 169000],
                        ['label' => '3m', 'storage' => '3m', 'color' => 'Black', 'price' => 199000],
                    ],
                ],
                [
                    'line' => 'MagGo Wireless Charger',
                    'items' => [
                        ['label' => 'Pad', 'storage' => '1 Pack', 'color' => 'White', 'price' => 499000],
                        ['label' => 'Stand', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 599000],
                        ['label' => '2-in-1', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 799000],
                        ['label' => '3-in-1', 'storage' => '1 Pack', 'color' => 'White', 'price' => 1199000],
                        ['label' => 'Car Mount', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 659000],
                    ],
                ],
            ],
            'UGREEN' => [
                [
                    'line' => 'GaN Charger',
                    'items' => [
                        ['label' => '20W', 'storage' => '20W', 'color' => 'White', 'price' => 279000],
                        ['label' => '30W', 'storage' => '30W', 'color' => 'White', 'price' => 349000],
                        ['label' => '65W', 'storage' => '65W', 'color' => 'Gray', 'price' => 699000],
                        ['label' => '100W', 'storage' => '100W', 'color' => 'Gray', 'price' => 999000],
                        ['label' => '140W', 'storage' => '140W', 'color' => 'Gray', 'price' => 1399000],
                    ],
                ],
                [
                    'line' => 'USB-C Cable',
                    'items' => [
                        ['label' => '0.5m', 'storage' => '0.5m', 'color' => 'Gray', 'price' => 79000],
                        ['label' => '1m', 'storage' => '1m', 'color' => 'Gray', 'price' => 99000],
                        ['label' => '1.5m', 'storage' => '1.5m', 'color' => 'Gray', 'price' => 119000],
                        ['label' => '2m', 'storage' => '2m', 'color' => 'Gray', 'price' => 139000],
                        ['label' => '3m', 'storage' => '3m', 'color' => 'Gray', 'price' => 159000],
                    ],
                ],
                [
                    'line' => 'Hub',
                    'items' => [
                        ['label' => '4-in-1', 'storage' => '4-in-1', 'color' => 'Gray', 'price' => 499000],
                        ['label' => '5-in-1', 'storage' => '5-in-1', 'color' => 'Gray', 'price' => 599000],
                        ['label' => '7-in-1', 'storage' => '7-in-1', 'color' => 'Gray', 'price' => 799000],
                        ['label' => '9-in-1', 'storage' => '9-in-1', 'color' => 'Gray', 'price' => 999000],
                        ['label' => '11-in-1', 'storage' => '11-in-1', 'color' => 'Gray', 'price' => 1299000],
                    ],
                ],
                [
                    'line' => 'Power Bank',
                    'items' => [
                        ['label' => '5000mAh', 'storage' => '5000mAh', 'color' => 'Black', 'price' => 299000],
                        ['label' => '10000mAh', 'storage' => '10000mAh', 'color' => 'Black', 'price' => 449000],
                        ['label' => '20000mAh', 'storage' => '20000mAh', 'color' => 'Gray', 'price' => 699000],
                        ['label' => '30000mAh', 'storage' => '30000mAh', 'color' => 'Gray', 'price' => 899000],
                        ['label' => 'MagSafe 10000mAh', 'storage' => '10000mAh', 'color' => 'White', 'price' => 899000],
                    ],
                ],
            ],
            'Baseus' => [
                [
                    'line' => 'GaN Fast Charger',
                    'items' => [
                        ['label' => '20W', 'storage' => '20W', 'color' => 'White', 'price' => 249000],
                        ['label' => '30W', 'storage' => '30W', 'color' => 'White', 'price' => 299000],
                        ['label' => '65W', 'storage' => '65W', 'color' => 'Black', 'price' => 599000],
                        ['label' => '100W', 'storage' => '100W', 'color' => 'Black', 'price' => 899000],
                        ['label' => '140W', 'storage' => '140W', 'color' => 'Black', 'price' => 1199000],
                    ],
                ],
                [
                    'line' => 'Cable',
                    'items' => [
                        ['label' => '0.5m', 'storage' => '0.5m', 'color' => 'Orange', 'price' => 69000],
                        ['label' => '1m', 'storage' => '1m', 'color' => 'Orange', 'price' => 89000],
                        ['label' => '1.5m', 'storage' => '1.5m', 'color' => 'Orange', 'price' => 109000],
                        ['label' => '2m', 'storage' => '2m', 'color' => 'Orange', 'price' => 129000],
                        ['label' => '3m', 'storage' => '3m', 'color' => 'Orange', 'price' => 149000],
                    ],
                ],
                [
                    'line' => 'Power Bank',
                    'items' => [
                        ['label' => '5000mAh', 'storage' => '5000mAh', 'color' => 'Black', 'price' => 279000],
                        ['label' => '10000mAh', 'storage' => '10000mAh', 'color' => 'Black', 'price' => 399000],
                        ['label' => '20000mAh', 'storage' => '20000mAh', 'color' => 'Gray', 'price' => 649000],
                        ['label' => '30000mAh', 'storage' => '30000mAh', 'color' => 'Gray', 'price' => 849000],
                        ['label' => 'Magsafe 10000mAh', 'storage' => '10000mAh', 'color' => 'White', 'price' => 849000],
                    ],
                ],
                [
                    'line' => 'Car Charger',
                    'items' => [
                        ['label' => 'Dual Port', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 199000],
                        ['label' => 'Fast Charge', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 249000],
                        ['label' => '65W', 'storage' => '65W', 'color' => 'Black', 'price' => 399000],
                        ['label' => '100W', 'storage' => '100W', 'color' => 'Black', 'price' => 599000],
                        ['label' => 'Cigarette Socket', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 179000],
                    ],
                ],
            ],
            'Belkin' => [
                [
                    'line' => 'BoostCharge',
                    'items' => [
                        ['label' => '20W', 'storage' => '20W', 'color' => 'White', 'price' => 329000],
                        ['label' => '30W', 'storage' => '30W', 'color' => 'White', 'price' => 429000],
                        ['label' => '45W', 'storage' => '45W', 'color' => 'White', 'price' => 599000],
                        ['label' => '65W', 'storage' => '65W', 'color' => 'White', 'price' => 799000],
                        ['label' => '100W', 'storage' => '100W', 'color' => 'White', 'price' => 1099000],
                    ],
                ],
                [
                    'line' => 'Cable',
                    'items' => [
                        ['label' => '0.5m', 'storage' => '0.5m', 'color' => 'White', 'price' => 99000],
                        ['label' => '1m', 'storage' => '1m', 'color' => 'White', 'price' => 129000],
                        ['label' => '1.5m', 'storage' => '1.5m', 'color' => 'White', 'price' => 149000],
                        ['label' => '2m', 'storage' => '2m', 'color' => 'White', 'price' => 169000],
                        ['label' => '3m', 'storage' => '3m', 'color' => 'White', 'price' => 199000],
                    ],
                ],
                [
                    'line' => 'Wireless Charger',
                    'items' => [
                        ['label' => 'Pad', 'storage' => '1 Pack', 'color' => 'White', 'price' => 599000],
                        ['label' => 'Stand', 'storage' => '1 Pack', 'color' => 'White', 'price' => 699000],
                        ['label' => '2-in-1', 'storage' => '1 Pack', 'color' => 'White', 'price' => 999000],
                        ['label' => '3-in-1', 'storage' => '1 Pack', 'color' => 'White', 'price' => 1399000],
                        ['label' => 'Car Mount', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 699000],
                    ],
                ],
                [
                    'line' => 'Screen Protector',
                    'items' => [
                        ['label' => '1 Pack', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 89000],
                        ['label' => '2 Pack', 'storage' => '2 Pack', 'color' => 'Transparent', 'price' => 129000],
                        ['label' => 'Privacy', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 179000],
                        ['label' => 'Anti-Glare', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 159000],
                        ['label' => 'Camera Lens', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 99000],
                    ],
                ],
            ],
            'ESR' => [
                [
                    'line' => 'Case',
                    'items' => [
                        ['label' => 'Clear', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 149000],
                        ['label' => 'MagSafe', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 199000],
                        ['label' => 'Armor', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 249000],
                        ['label' => 'Rugged', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 299000],
                        ['label' => 'HaloLock', 'storage' => '1 Pack', 'color' => 'White', 'price' => 259000],
                    ],
                ],
                [
                    'line' => 'Screen Protector',
                    'items' => [
                        ['label' => '1 Pack', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 99000],
                        ['label' => '2 Pack', 'storage' => '2 Pack', 'color' => 'Transparent', 'price' => 149000],
                        ['label' => 'Privacy', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 199000],
                        ['label' => 'Camera Lens', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 89000],
                        ['label' => 'Air Guard', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 129000],
                    ],
                ],
                [
                    'line' => 'MagSafe Charger',
                    'items' => [
                        ['label' => '15W', 'storage' => '15W', 'color' => 'White', 'price' => 399000],
                        ['label' => '20W', 'storage' => '20W', 'color' => 'White', 'price' => 499000],
                        ['label' => '2-in-1', 'storage' => '1 Pack', 'color' => 'White', 'price' => 899000],
                        ['label' => '3-in-1', 'storage' => '1 Pack', 'color' => 'White', 'price' => 1299000],
                        ['label' => 'Stand', 'storage' => '1 Pack', 'color' => 'White', 'price' => 699000],
                    ],
                ],
                [
                    'line' => 'Kickstand',
                    'items' => [
                        ['label' => 'Pocket', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 99000],
                        ['label' => 'Magnetic', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 149000],
                        ['label' => 'Adjustable', 'storage' => '1 Pack', 'color' => 'Gray', 'price' => 199000],
                        ['label' => 'Foldable', 'storage' => '1 Pack', 'color' => 'Gray', 'price' => 179000],
                        ['label' => 'Desk', 'storage' => '1 Pack', 'color' => 'White', 'price' => 229000],
                    ],
                ],
            ],
            'Spigen' => [
                [
                    'line' => 'Case',
                    'items' => [
                        ['label' => 'Slim Armor', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 199000],
                        ['label' => 'Ultra Hybrid', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 179000],
                        ['label' => 'Tough Armor', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 239000],
                        ['label' => 'Liquid Air', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 169000],
                        ['label' => 'Mag Armor', 'storage' => '1 Pack', 'color' => 'White', 'price' => 249000],
                    ],
                ],
                [
                    'line' => 'Screen Protector',
                    'items' => [
                        ['label' => '1 Pack', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 89000],
                        ['label' => '2 Pack', 'storage' => '2 Pack', 'color' => 'Transparent', 'price' => 129000],
                        ['label' => 'EZ Fit', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 159000],
                        ['label' => 'Privacy', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 199000],
                        ['label' => 'Camera Lens', 'storage' => '1 Pack', 'color' => 'Transparent', 'price' => 99000],
                    ],
                ],
                [
                    'line' => 'Car Mount',
                    'items' => [
                        ['label' => 'Vent', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 199000],
                        ['label' => 'Dash', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 249000],
                        ['label' => 'Magnetic', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 299000],
                        ['label' => 'Wireless', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 499000],
                        ['label' => 'Pro', 'storage' => '1 Pack', 'color' => 'Black', 'price' => 399000],
                    ],
                ],
                [
                    'line' => 'Charger',
                    'items' => [
                        ['label' => '20W', 'storage' => '20W', 'color' => 'White', 'price' => 259000],
                        ['label' => '30W', 'storage' => '30W', 'color' => 'White', 'price' => 329000],
                        ['label' => '45W', 'storage' => '45W', 'color' => 'White', 'price' => 499000],
                        ['label' => '65W', 'storage' => '65W', 'color' => 'White', 'price' => 699000],
                        ['label' => '100W', 'storage' => '100W', 'color' => 'White', 'price' => 999000],
                    ],
                ],
            ],
        ];
    }

    private function slugCode(string $value): string
    {
        $code = Str::upper(Str::ascii($value));
        $code = preg_replace('/[^A-Z0-9]+/', '', $code) ?: '';

        return $code ?: 'NA';
    }

    public function run(): void
    {
        $category = Category::where('slug', 'phu-kien')->firstOrFail();

        foreach ($this->brandCatalog() as $brandName => $groups) {
            $brand = Brand::where('name', $brandName)->where('type', 'accessory')->firstOrFail();
            $brandCode = $this->slugCode($brandName);

            foreach ($groups as $groupIndex => $group) {
                foreach ($group['items'] as $itemIndex => $item) {
                    $name = sprintf('%s %s %s', $brandName, $group['line'], $item['label']);
                    $slug = Str::slug($name);
                    $productCode = $this->slugCode($group['line']);
                    $itemCode = $this->slugCode($item['label']);

                    $productAttributes = Product::factory()
                        ->accessoryForBrand(
                            $brand,
                            $category,
                            $name,
                            [
                                'slug' => $slug,
                            ]
                        )
                        ->raw();

                    $product = Product::updateOrCreate(
                        ['slug' => $slug],
                        $productAttributes
                    );

                    $sku = implode('-', ['ACC', $brandCode, $productCode, $itemCode]);

                    $variantAttributes = ProductVariant::factory()
                        ->accessoryForProduct(
                            $product,
                            [
                                'storage' => $item['storage'],
                                'color' => $item['color'],
                                'price' => $item['price'],
                                'is_featured' => $groupIndex === 0 && $itemIndex < 2,
                                'sale_price' => $item['price'] > 0 && $itemIndex % 3 === 0
                                    ? max(0, $item['price'] - 20000)
                                    : null,
                                'quantity' => 20 + (($groupIndex + $itemIndex) % 30),
                                'sku' => $sku,
                                'description' => $name . ' ' . $item['storage'],
                            ]
                        )
                        ->raw();

                    ProductVariant::updateOrCreate(
                        ['sku' => $sku],
                        $variantAttributes
                    );
                }
            }
        }
    }
}
