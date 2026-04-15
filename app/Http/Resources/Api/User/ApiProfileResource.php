<?php

namespace App\Http\Resources\Api\User;

use Illuminate\Http\Request;
use App\Http\Resources\Api\ApiStatusResource;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiProfileResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'name',
        'phone',
        'email',
        'gender',
        'region',
        'province',
        'city',
        'barangay',
        'street',
        'postal_code',
        'valid_id_type',
        'valid_id_number',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'status' => ApiStatusResource::class,
        'userType' => ApiUserResource::class,
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate,

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
            'status' => $this->whenLoaded('status', fn() => [
                'id' => $this->status->id,
                'name' => $this->status->name,
            ]),
            'user_type' => $this->whenLoaded('userType', fn() => [
                'id' => $this->userType->id,
                'name' => $this->userType->name,
            ]),
        ];
    }
}
