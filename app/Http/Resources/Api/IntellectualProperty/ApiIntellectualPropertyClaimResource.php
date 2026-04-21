<?php

namespace App\Http\Resources\Api\IntellectualProperty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiIntellectualPropertyClaimResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'description',
    ];

    /**
     * The resource's attributes.
     */
    public function toAttributes(Request $request): array
    {
        return [
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'intellectual_property' => ApiIntellectualPropertyResource::class
    ];
}
