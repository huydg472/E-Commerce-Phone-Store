<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'storage',
        'ram',
        'sku',
        'import_price',
        'price',
        'sale_price',
        'quantity',
        'reserved_quantity',
        'status',
        'is_featured',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'import_price' => 'decimal:2',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    protected $appends = [
        'available_quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductVariantImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max((int)$this->quantity - (int)$this->reserved_quantity, 0);
    }
}
