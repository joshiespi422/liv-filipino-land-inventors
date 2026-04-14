<?php

namespace App\Http\Resources\Api\BusinessTraining;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CategoryResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'name',
        'slug',
        'description',
        'created_at',
        'updated_at',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'type' => TypeResource::class,
        'trainings' => TrainingResource::class,
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->whenLoaded('type', fn() => $this->type->icon),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
