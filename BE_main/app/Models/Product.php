<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'slug',
        'thumbnail_url',
        'short_description',
        'description',
        'status',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    public function productSpecifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }
}
