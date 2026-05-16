<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantImage extends Model
{
    /** @use HasFactory<Database\Factories\ProductVariantImageFactory.php> */
    use HasFactory;

    protected $fillable = [
        'image_url',
        'alt_text',
        'sort_order'
    ];

    public function product_variants()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
