<?php

namespace App\Http\Requests\NewsEvents;

use App\Enums\NewsArticleStatus;
use App\Models\NewsArticle;
use App\Models\UserType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->user_type_id === UserType::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var NewsArticle $article */
        $article = $this->route('article');
        $current = $article->status;

        // current status (no-op) + the transitions the enum allows from it.
        // Validated against the fresh DB status, so a stale page (e.g. the cron
        // just published the article) is rejected instead of corrupting state.
        $allowedStatuses = collect([$current, ...$current->transitions()])
            ->pluck('value')->unique()->values()->all();

        // archived articles are frozen: content fields are rejected outright
        $editable = $current->isEditable();
        $content = fn (array $rules): array => $editable ? ['sometimes', ...$rules] : ['prohibited'];

        $scheduled = $this->input('status') === NewsArticleStatus::SCHEDULED->value;

        return [
            'news_category_id' => $content([
                'required',
                'integer',
                // active categories, plus the article's current one even if it was deactivated since
                Rule::exists('news_categories', 'id')->where(
                    fn ($q) => $q->where(
                        fn ($g) => $g->where('is_active', true)->orWhere('id', $article->news_category_id)
                    )
                ),
            ]),
            'title' => $content(['required', 'string', 'max:255']),
            'content' => $content(['required', 'string']),
            'image' => $content(['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']),
            'source_name' => $content(['nullable', 'string', 'max:255']),
            'source_url' => $content(['nullable', 'url:http,https', 'max:2048']),

            'status' => ['sometimes', 'required', Rule::in($allowedStatuses)],
            'published_at' => $scheduled
                ? ['required', 'date_format:Y-m-d', 'after:today']
                : ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => "That status change isn't allowed for this article.",
            'published_at.required' => 'Pick a date to schedule this article.',
            'published_at.after' => 'The scheduled date must be tomorrow or later.',
        ];
    }
}
