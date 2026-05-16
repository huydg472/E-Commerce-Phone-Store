<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'color',
        'storage',
        'ram',
        'sku',
        'import_price',
        'price',
        'sale_price',
        'quantity',
        'status',
        'description',
    ];

    public function products()
    {
        return $this->belongsTo(Product::class);
    }

    public function products_variant_images()
    {
        return $this->hasMany(ProductVariantImage::class);
    }

    public function cart_items()
    {
        return $this->hasMany(CartItem::class);
    }
    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function stock_logs()
    {
        return $this->hasMany(StockLog::class);
    }
}
