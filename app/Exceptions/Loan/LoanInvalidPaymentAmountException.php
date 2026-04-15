<?php

namespace App\Exceptions\Loan;

use Exception;

class LoanInvalidPaymentAmountException extends Exception
{
    public function __construct(
        public float $required,
        public float $provided
    ) {
        parent::__construct('Payment must be exact full remaining balance.');
    }

    public function render()
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
