<?php

namespace App\Http\Resources\Api\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiUserTypeResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'id',
        'name',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        // ...
    ];
}
