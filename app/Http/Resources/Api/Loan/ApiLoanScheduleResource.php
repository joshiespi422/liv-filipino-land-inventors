<?php

namespace App\Http\Resources\Api\Loan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiLoanScheduleResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'month_no',
        'beginning_balance',
        'interest_amount',
        'principal_amount',
        'total_payment',
        'ending_balance',
        'due_date',
        'status',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'month_no' => $this->month_no,
            'beginning_balance' => number_format($this->beginning_balance, 2, '.', ''),
            'interest_amount' => number_format($this->interest_amount, 2, '.', ''),
            'principal_amount' => number_format($this->principal_amount, 2, '.', ''),
            'total_payment' => number_format($this->total_payment, 2, '.', ''),
            'ending_balance' => number_format($this->ending_balance, 2, '.', ''),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status?->name ?? 'Unknown',
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'payments' => ApiLoanPaymentResource::class,
    ];

}
