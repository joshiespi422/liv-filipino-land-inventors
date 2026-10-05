<?php

namespace App\Http\Requests\NewsEvents;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\NewsArticleStatus;
use App\Models\UserType;

class StoreNewsArticleRequest extends FormRequest
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
        $scheduled = $this->input('status') === NewsArticleStatus::SCHEDULED->value;

        return [
            'news_category_id' => [
                'required',
                'integer',
                Rule::exists('news_categories', 'id')->where('is_active', true),
            ],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => [
                'required',
                Rule::enum(NewsArticleStatus::class)->only(NewsArticleStatus::creatable()),
            ],
            // only validated when scheduled; ignored for draft/published
            'published_at' => $scheduled
                ? ['required', 'date_format:Y-m-d', 'after:today']
                : ['nullable'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'published_at.required' => 'Pick a date to schedule this article.',
            'published_at.after' => 'The scheduled date must be tomorrow or later.',
        ];
    }
}
