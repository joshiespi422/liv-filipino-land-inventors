<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\NewsArticleStatus;

#[Fillable([
    'news_category_id',
    'user_id',
    'title',
    'slug',
    'content',
    'image',
    'status',
    'views_count',
    'source_name',
    'source_url',
    'published_at',
])]
class NewsArticle extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => NewsArticleStatus::class,
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function newsCategory(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
