<?php

namespace App\Http\Resources\Api\BusinessTraining;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class TrainingResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'module',
        'content',
        'created_at',
        'updated_at',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'category' => CategoryResource::class,
    ];
}
