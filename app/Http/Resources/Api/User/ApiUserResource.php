<?php

namespace App\Http\Resources\Api\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiUserResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'name',
        'phone',
        'email',
        'gender',
        'birthdate',
        'region',
        'province',
        'city',
        'barangay',
        'street',
        'postal_code',
        'avatar',
        'valid_id_type',
        'valid_id_number',
        'front_valid_id_picture',
        'back_valid_id_picture',
        'is_verified',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate?->format('Y-m-d'),

            'region' => $this->region,
            'province' => $this->province,
            'city' => $this->city,
            'barangay' => $this->barangay,
            'street' => $this->street,
            'postal_code' => $this->postal_code,

            'avatar' => fn() => $this->avatar
                ? asset('storage/' . $this->avatar)
                : null,

            'valid_id_type' => $this->valid_id_type,
            'valid_id_number' => $this->valid_id_number,

            'front_valid_id_picture' => fn() => $this->front_valid_id_picture
                ? asset('storage/' . $this->front_valid_id_picture)
                : null,

            'back_valid_id_picture' => fn() => $this->back_valid_id_picture
                ? asset('storage/' . $this->back_valid_id_picture)
                : null,

            'is_verified' => fn() => !is_null($this->valid_id_number),
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'status' => StatusResource::class,
        'userType' => ApiUserTypeResource::class,
    ];
}
