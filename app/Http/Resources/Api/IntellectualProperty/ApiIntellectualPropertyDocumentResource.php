<?php

namespace App\Http\Resources\Api\IntellectualProperty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiIntellectualPropertyDocumentResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'attachment'
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'attachment' => $this->attachment,
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'intellectual_property' => ApiIntellectualPropertyResource::class
    ];
}
