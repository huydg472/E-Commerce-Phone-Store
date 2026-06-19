<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantImage extends Model
{
    protected $fillable = [
        'product_variant_id',
        'image_url',
        'alt_text',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
