<?php

namespace App\Http\Resources\Api\Loan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiLoanPaymentResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'amount',
        'payment_date',
        'payment_method',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'amount' => number_format($this->amount, 2, '.', ''),
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'payment_method' => $this->paymentMethod?->name ?? null,
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        // ...
    ];
}
