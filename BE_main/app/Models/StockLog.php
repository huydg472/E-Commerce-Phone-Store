<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    /** @use HasFactory<\Database\Factories\StockLogFactory> */
    use HasFactory;
    protected $fillable = [
        'product_variant_id',
        'user_id',
        'order_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'note',
    ];
    public function product_variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order_id()
    {
        return $this->belongsTo(Order::class);
    }
}
