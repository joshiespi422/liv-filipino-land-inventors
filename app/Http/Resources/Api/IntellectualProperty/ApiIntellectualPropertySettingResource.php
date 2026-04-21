<?php

namespace App\Http\Resources\Api\IntellectualProperty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiIntellectualPropertySettingResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'amount',
        'allowed_term_months',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'intellectual_property' => ApiIntellectualPropertyResource::class,
    ];
}
