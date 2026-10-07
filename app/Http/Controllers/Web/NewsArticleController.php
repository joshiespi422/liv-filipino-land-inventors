<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Http\Resources\NewsArticleResource;
use App\Http\Resources\NewsArticleDetailResource;
use App\Http\Requests\NewsEvents\UpdateNewsArticleRequest;
use App\Http\Requests\NewsEvents\StoreNewsArticleRequest;
use App\Enums\NewsArticleStatus;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;
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
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        // Set defaults
        $filters = [
            'status' => $validated['status'] ?? null,
            'category' => $validated['category'] ?? null,
            'search' => isset($validated['search']) ? trim($validated['search']) : null,
        ];

        // Build and execute query
        $articles = NewsArticle::query()
            ->with('newsCategory:id,name')
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
            ->when(
                filled($filters['search']),
                // escape LIKE wildcards so "100%" or "a_b" are matched literally
                fn (Builder $q) => $q->where(
                    'title',
                    'like',
                    '%'.addcslashes($filters['search'], '\\%_').'%'
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

    public function create(): Response
    {
        abort_unless($this->canMutate(), 403);

        return Inertia::render('news-events/Create', [
            'news_categories' => NewsCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'statuses' => collect(NewsArticleStatus::creatable())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->values(),
        ]);
    }

    public function store(StoreNewsArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $status = NewsArticleStatus::from($data['status']);

        $path = $request->file('image')->store('news-articles/images', 'public');

        try {
            $article = NewsArticle::create([
                'news_category_id' => $data['news_category_id'],
                'user_id' => $request->user()->id,
                'title' => $data['title'],
                'slug' => NewsArticle::generateUniqueSlug($data['title']),
                'content' => $data['content'],
                'image' => $path,
                'status' => $status,
                'source_name' => $data['source_name'] ?? null,
                'source_url' => $data['source_url'] ?? null,
                'published_at' => match ($status) {
                    NewsArticleStatus::DRAFT => null,
                    NewsArticleStatus::SCHEDULED => Carbon::createFromFormat('Y-m-d', $data['published_at'])->startOfDay(),
                    default => now(),
                },
            ]);
        } catch (Throwable $e) {
            // don't leave an orphaned upload behind if the insert fails
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return redirect()->route('news-events.show', $article->slug);
    }

    public function show(NewsArticle $article)
    {
        $article->loadMissing([
            'newsCategory:id,name',
            'user:id,name',
        ]);

        $canMutate = $this->canMutate();

        return Inertia::render('news-events/Show', [
            'article' => NewsArticleDetailResource::make($article)->resolve(),
            'can_mutate' => $canMutate,
            'abilities' => [
                'edit' => $canMutate && $article->status->isEditable(),
                'delete' => $canMutate && $article->status->isDeletable(),
                'transitions' => $canMutate
                    ? collect($article->status->transitions())->map(fn ($s) => [
                        'value' => $s->value,
                        'label' => $s->label(),
                    ])->values()
                    : [],
            ],
        ]);
    }

    public function edit(NewsArticle $article): Response
    {
        abort_unless($this->canMutate() && $article->status->isEditable(), 403);

        $article->loadMissing(['newsCategory:id,name', 'user:id,name']);

        return Inertia::render('news-events/Edit', [
            'article' => NewsArticleDetailResource::make($article)->resolve(),
            'news_categories' => NewsCategory::query()
                ->where('is_active', true)
                ->orWhere('id', $article->news_category_id)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(UpdateNewsArticleRequest $request, NewsArticle $article): RedirectResponse
    {
        $data = $request->validated();
        $attributes = [];
        $newImage = $oldImage = null;

        // content edits (the request already rejects them for archived articles)
        if ($article->status->isEditable()) {
            $attributes = Arr::only($data, [
                'news_category_id', 'title', 'content', 'source_name', 'source_url',
            ]);

            if ($request->hasFile('image')) {
                $newImage = $request->file('image')->store('news-articles', 'public');
                $oldImage = $article->image;
                $attributes['image'] = $newImage;
            }
        }

        // status change
        if (isset($data['status'])) {
            $target = NewsArticleStatus::from($data['status']);

            $attributes['status'] = $target;
            $attributes['published_at'] = $this->resolvePublishedAt(
                $article,
                $target,
                $data['published_at'] ?? null,
            );
        }

        try {
            $article->update($attributes);
        } catch (Throwable $e) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }
            throw $e;
        }

        // only remove the replaced image once the update has succeeded
        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return to_route('news-events.show', $article->slug);
    }

    public function destroy(NewsArticle $article): RedirectResponse
    {
        abort_unless($this->canMutate(), 403);
        abort_unless($article->status->isDeletable(), 403, 'Archive a published article before deleting it.');

        $image = $article->image;

        $article->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return to_route('news-events.index');
    }

    private function resolvePublishedAt(NewsArticle $article, NewsArticleStatus $target, ?string $date): ?Carbon
    {
        return match ($target) {
            NewsArticleStatus::DRAFT => null,
            NewsArticleStatus::SCHEDULED => Carbon::createFromFormat('Y-m-d', $date)->startOfDay(),
            // publishing a draft/scheduled article stamps "now"; restoring an archived
            // one keeps its original date
            NewsArticleStatus::PUBLISHED => in_array($article->status, [NewsArticleStatus::DRAFT, NewsArticleStatus::SCHEDULED], true)
                ? now()
                : ($article->published_at ?? now()),
            NewsArticleStatus::ARCHIVED => $article->published_at ?? now(),
        };
    }

}
