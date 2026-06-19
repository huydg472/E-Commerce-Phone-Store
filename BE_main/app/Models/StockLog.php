<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_variant_id',
        'user_id',
        'order_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before' => 'integer',
            'quantity_change' => 'integer',
            'quantity_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
