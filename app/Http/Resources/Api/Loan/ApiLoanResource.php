<?php

namespace App\Http\Resources\Api\Loan;

use App\Http\Resources\Api\Loan\ApiLoanScheduleResource;
use App\Http\Resources\Api\User\ApiUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiLoanResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'amount',
        'interest_rate',
        'term_months',
        'monthly_principal',
        'start_date',
        'end_date',
        'status',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'amount' => number_format($this->amount, 2, '.', ''),
            'interest_rate' => number_format($this->interest_rate, 2, '.', '') . '%',
            'term_months' => $this->term_months,
            'monthly_principal' => number_format($this->monthly_principal, 2, '.', ''),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status?->name ?? 'Unknown',
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'user' => ApiUserResource::class,
        'loanSchedules' => ApiLoanScheduleResource::class,
    ];
}
