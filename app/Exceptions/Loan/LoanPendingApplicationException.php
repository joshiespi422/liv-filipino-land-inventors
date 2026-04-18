<?php

namespace App\Exceptions\Loan;

use App\Models\Loan;
use Exception;
use Illuminate\Http\JsonResponse;

class LoanPendingApplicationException extends Exception
{
    public function __construct(
        public readonly Loan $loan,
        public readonly ?int $scheduleId = null
    ) {
        parent::__construct(
            'This loan has not been approved by the admin yet. Please wait for approval before proceeding.'
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => [
                'loan_id' => $this->loan->id,
                'schedule_id' => $this->scheduleId,
                'status' => 'PENDING',
            ],
        ], 422);
    }
}
