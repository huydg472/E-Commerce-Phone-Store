<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use Illuminate\Database\Seeder;

class ProductVariantImageSeeder extends Seeder
{
    public function run(): void
    {
        ProductVariant::with('product')->get()->each(function (ProductVariant $variant): void {
            for ($i = 1; $i <= 2; $i++) {
                $imageUrl = 'https://placehold.co/600x600?text=' . rawurlencode($variant->sku . '-' . $i);

                ProductVariantImage::updateOrCreate(
                    [
                        'product_variant_id' => $variant->id,
                        'image_url' => $imageUrl,
                    ],
                    [
                        'product_variant_id' => $variant->id,
                        'image_url' => $imageUrl,
                        'alt_text' => $variant->product?->name . ' ' . $variant->color,
                        'sort_order' => $i - 1,
                    ]
                );
            }
        });
    }
}
