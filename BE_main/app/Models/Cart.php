<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Thêm hàm này để controller có thể gọi with(['items...'])
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
