<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\NewsArticleStatus;
use Illuminate\Support\Str;

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

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::limit(Str::slug($title) ?: 'article', 240, '');
        $slug = $base;

        // "create" is reserved, article with that slug could never be opened
        while ($slug === 'create' || static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(5));
        }

        return $slug;
    }
}
