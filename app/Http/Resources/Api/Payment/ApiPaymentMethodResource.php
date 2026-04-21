<?php

namespace App\Http\Resources\Api\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiPaymentMethodResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'name',
        'gateway_type',
        'is_offline',
        'is_client_side',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        // ...
    ];
}
