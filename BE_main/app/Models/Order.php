<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'shipping_address_id',
        'order_code',
        'receiver_name',
        'receiver_phone',
        'shipping_address_text',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'payment_status',
        'order_status',
        'note',
        'ordered_at',
        'cancelled_at',
        'completed_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shipping_address()
    {
        return $this->belongsTo(ShippingAddress::class);
    }
}
