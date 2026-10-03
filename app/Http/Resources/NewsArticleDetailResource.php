<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class NewsArticleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->newsCategory?->name,
            'user' => $this->user?->name,
            'title' => $this->title,
            'content' => $this->content,
            'image' => $this->image ? Storage::url($this->image) : null,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'views_count' => $this->views_count,
            'source_name' => $this->source_name ?? null,
            'source_url' => $this->source_url ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
