<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    private function codePart(string $value): string
    {
        $code = Str::upper(Str::ascii($value));
        $code = preg_replace('/[^A-Z0-9]+/', '', $code) ?: '';

        return $code ?: 'NA';
    }

    private function skuFor(Product $product, array $variant): string
    {
        $tokens = preg_split('/[^A-Z0-9]+/', Str::upper(Str::ascii($product->slug)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $productCode = collect($tokens)
            ->map(fn (string $token) => preg_match('/\d/', $token) ? $token : Str::substr($token, 0, 1))
            ->join('');

        return implode('-', [
            $productCode ?: 'SP' . $product->id,
            $this->codePart($variant['color']),
            $this->codePart($variant['storage']),
            $this->codePart($variant['ram']),
        ]);
    }

    private function profileFor(Product $product): array
    {
        $brand = $product->brand?->name;
        $name = $product->name;

        if ($brand === 'Apple') {
            $isProMax = str_contains($name, 'Pro Max');
            $isPro = str_contains($name, 'Pro');
            $isPlus = str_contains($name, 'Plus');
            $isMini = str_contains($name, 'mini');
            $isOld = preg_match('/iPhone (8|X|XR|XS|11)/', $name);

            return [
                'colors' => $isPro
                    ? ['Black Titanium', 'White Titanium', 'Natural Titanium', 'Blue Titanium']
                    : ($isOld ? ['Black', 'White', 'Red'] : ['Black', 'Blue', 'Pink', 'Green']),
                'storages' => $isProMax ? ['256GB', '512GB', '1TB'] : ($isMini || $isOld ? ['64GB', '128GB', '256GB'] : ['128GB', '256GB', '512GB']),
                'ram' => str_contains($name, '16') ? '8GB' : ($isOld ? '4GB' : '6GB'),
                'base_price' => $isProMax ? 34990000 : ($isPro ? 28990000 : ($isPlus ? 25990000 : ($isOld ? 8990000 : 19990000))),
            ];
        }

        if ($brand === 'Samsung') {
            $isUltra = str_contains($name, 'Ultra');
            $isFold = str_contains($name, 'Fold');
            $isFlip = str_contains($name, 'Flip');
            $isFlagship = str_contains($name, 'Galaxy S') || $isFold || $isFlip;
            $isBudget = str_contains($name, 'A1') || str_contains($name, 'A2') || str_contains($name, 'M15');

            return [
                'colors' => $isFlagship
                    ? ['Phantom Black', 'Cream', 'Green', 'Lavender']
                    : ['Black', 'Blue', 'Light Violet'],
                'storages' => $isUltra || $isFold ? ['256GB', '512GB', '1TB'] : ($isBudget ? ['128GB', '256GB'] : ['128GB', '256GB', '512GB']),
                'ram' => $isUltra || $isFold || $isFlip ? '12GB' : ($isBudget ? '6GB' : '8GB'),
                'base_price' => $isFold ? 41990000 : ($isFlip ? 28990000 : ($isUltra ? 28990000 : ($isFlagship ? 19990000 : ($isBudget ? 4990000 : 8990000)))),
            ];
        }

        if ($brand === 'Xiaomi') {
            $isFlagship = str_contains($name, 'Xiaomi 14') || str_contains($name, 'Ultra') || str_contains($name, 'Pro');
            $isPoco = str_contains($name, 'POCO');
            $isEntry = str_contains($name, 'Redmi A') || str_contains($name, '13C');

            return [
                'colors' => $isPoco ? ['Black', 'Yellow', 'Blue'] : ['Black', 'Green', 'Blue'],
                'storages' => $isEntry ? ['64GB', '128GB'] : ($isFlagship ? ['256GB', '512GB'] : ['128GB', '256GB']),
                'ram' => $isFlagship ? '12GB' : ($isEntry ? '4GB' : '8GB'),
                'base_price' => $isFlagship ? 15990000 : ($isPoco ? 8990000 : ($isEntry ? 2490000 : 5490000)),
            ];
        }

        if ($brand === 'OPPO') {
            $isFind = str_contains($name, 'Find');
            $isReno = str_contains($name, 'Reno');
            $isA = str_contains($name, ' A');

            return [
                'colors' => $isFind ? ['Space Black', 'Pearl White', 'Star Grey'] : ($isReno ? ['Astro Silver', 'Matte Brown', 'Sunset Pink'] : ['Black', 'Blue', 'Green']),
                'storages' => $isFind ? ['256GB', '512GB'] : ($isA ? ['128GB', '256GB'] : ['256GB', '512GB']),
                'ram' => $isFind ? '16GB' : ($isA ? '8GB' : '12GB'),
                'base_price' => $isFind ? 22990000 : ($isReno ? 10990000 : ($isA ? 4990000 : 8990000)),
            ];
        }

        if ($brand === 'Vivo') {
            $isX = str_contains($name, 'X');
            $isV = str_contains($name, 'V');
            $isY = str_contains($name, 'Y');

            return [
                'colors' => $isX ? ['Asteroid Black', 'Startrail Blue', 'Sunset Orange'] : ($isV ? ['Stellar Silver', 'Nebula Purple', 'Crystal Black'] : ['Black', 'Green', 'Gold']),
                'storages' => $isY ? ['128GB', '256GB'] : ['256GB', '512GB'],
                'ram' => $isX ? '12GB' : ($isY ? '8GB' : '12GB'),
                'base_price' => $isX ? 19990000 : ($isV ? 11990000 : ($isY ? 4490000 : 7990000)),
            ];
        }

        $isGT = str_contains($name, 'GT');
        $isPro = str_contains($name, 'Pro');
        $isC = str_contains($name, ' C');

        return [
            'colors' => $isGT || $isPro ? ['Razor Green', 'Fluid Silver', 'Black'] : ['Starlight Black', 'Starlight Purple', 'Gold'],
            'storages' => $isC ? ['128GB', '256GB'] : ['256GB', '512GB'],
            'ram' => $isGT || $isPro ? '12GB' : '8GB',
            'base_price' => $isGT ? 14990000 : ($isPro ? 10990000 : ($isC ? 3990000 : 6990000)),
        ];
    }

    private function priceFor(int $basePrice, string $storage): int
    {
        $increments = [
            '64GB' => -1000000,
            '128GB' => 0,
            '256GB' => 2000000,
            '512GB' => 5000000,
            '1TB' => 9000000,
        ];

        return max(1990000, $basePrice + ($increments[$storage] ?? 0));
    }

    public function run(): void
    {
        Product::with('brand')->orderBy('id')->get()->values()->each(function (Product $product, int $productIndex): void {
            $profile = $this->profileFor($product);

            foreach ($profile['colors'] as $colorIndex => $color) {
                foreach ($profile['storages'] as $storageIndex => $storage) {
                    $variant = [
                        'color' => $color,
                        'storage' => $storage,
                        'ram' => $profile['ram'],
                    ];

                    $sku = $this->skuFor($product, $variant);
                    $price = $this->priceFor($profile['base_price'], $storage);
                    $salePrice = $price >= 10000000 ? $price - 1000000 : $price - 300000;

                    ProductVariant::updateOrCreate(
                        ['sku' => $sku],
                        [
                            'product_id' => $product->id,
                            'color' => $color,
                            'storage' => $storage,
                            'ram' => $profile['ram'],
                            'sku' => $sku,
                            'import_price' => (int) round($price * 0.82, -3),
                            'price' => $price,
                            'sale_price' => $salePrice,
                            'quantity' => 8 + (($product->id + $colorIndex + $storageIndex) % 28),
                            'status' => 'active',
                            'is_featured' => $productIndex < 5 && $colorIndex === 0 && $storageIndex === 0,
                            'description' => $product->name . ' ' . $color . ' ' . $storage . ' ' . $profile['ram'],
                        ]
                    );
                }
            }
        });
    }
}
