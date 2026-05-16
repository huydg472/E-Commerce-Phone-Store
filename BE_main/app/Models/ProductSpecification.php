<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model
{
    /** @use HasFactory<\Database\Factories\ProductSpecificationFactory> */
    use HasFactory;

    protected $fillable = [
        'spec_name',
        'spec_value',
        'sort_order',
    ];

    public function products()
    {
        $this->belongsTo(Product::class);
    }
}
