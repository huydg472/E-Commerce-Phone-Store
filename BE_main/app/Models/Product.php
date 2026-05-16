<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail_url',
        'short_description',
        'description',
        "is_featured",
        'status'
    ];

    public function brands()
    {
        $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        $this->belongsTo(Category::class);
    }

    public function product_variants()
    {
        $this->hasMany(ProductVariant::class);
    }

    public function product_specification()
    {
        $this->hasMany(ProductSpecification::class);
    }
}
