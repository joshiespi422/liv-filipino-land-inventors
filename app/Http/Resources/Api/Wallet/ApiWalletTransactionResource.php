<?php

namespace App\Http\Resources\Api\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiWalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'amount' => number_format($this->amount, 2, '.', ''),
            'type' => $this->type,
            'description' => $this->description,
            'reference_id' => $this->reference_id,
            'reference_type' => $this->reference_type,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
