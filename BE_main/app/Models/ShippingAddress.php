<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    /** @use HasFactory<\Database\Factories\ShippingAddressFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'receiver_name',
        'receicer-phong',
        'province',
        'district',
        'ward',
        'address_detail',
        'note',
        'is_default'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
