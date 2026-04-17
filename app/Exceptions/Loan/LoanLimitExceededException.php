<?php

namespace App\Exceptions\Loan;

use Exception;

class LoanLimitExceededException extends Exception
{
    public function __construct(
        public readonly float $limit,
    ) {
        parent::__construct('Loan amount exceeds your loanable limit.');
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => [
                'loanable_amount' => $this->limit,
            ],
        ], 422);
    }
}
