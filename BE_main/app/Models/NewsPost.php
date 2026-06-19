<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    protected $fillable = [
        'news_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image_url',
        'status',
        'is_featured',
        'reading_minutes',
        'views_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'reading_minutes' => 'integer',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }
}
