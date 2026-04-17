<?php

namespace App\Exceptions\Loan;

use Exception;

class LoanInvalidPaymentAmountException extends Exception
{
    public function __construct(
        public readonly float $required,
        public readonly float $provided,
    ) {
        parent::__construct('Payment must be the exact full remaining balance.');
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => [
                'required_amount' => $this->required,
                'provided_amount' => $this->provided,
            ],
        ], 422);
    }
}
