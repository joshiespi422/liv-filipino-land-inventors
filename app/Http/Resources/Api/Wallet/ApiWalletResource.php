<?php

namespace App\Http\Resources\Api\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiWalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array {
    return [
        'id'         => $this->id,
        'balance'    => number_format($this->balance, 2, '.', ''),
        'show'       => (bool) $this->show,
        'updated_at' => $this->updated_at->toDateTimeString(),
    ];
}
}
