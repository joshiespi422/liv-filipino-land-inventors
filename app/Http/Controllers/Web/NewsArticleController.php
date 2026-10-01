<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Http\Resources\NewsArticleResource;
use App\Enums\NewsArticleStatus;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\UserType;
use Inertia\Inertia;
use Inertia\Response;

class NewsArticleController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    public function index(Request $request): Response
    {
        // Validate all filters
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(NewsArticleStatus::class)],
            'category' => ['nullable', 'string', Rule::exists('news_categories', 'slug')],
        ]);

        // Set defaults
        $filters = [
            'status' => $validated['status'] ?? null,
            'category' => $validated['category'] ?? null,
        ];

        // Build and execute query
        $articles = NewsArticle::query()
            ->with('category:id,name')
            ->when(
                $filters['status'],
                fn (Builder $q, string $status) => $q->where('status', $status)
            )
            ->when(
                $filters['category'],
                fn (Builder $q, string $slug) => $q->whereHas(
                    'newsCategory',
                    fn (Builder $c) => $c->where('slug', $slug)
                )
            )
            ->latest()
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('news-events/Index', [
            'news_articles' => NewsArticleResource::collection($articles),
            'news_categories' => NewsCategory::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'statuses' => collect(NewsArticleStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->values(),
            'can_mutate' => $this->canMutate(),
            'filters' => $filters,
        ]);
    }

}
