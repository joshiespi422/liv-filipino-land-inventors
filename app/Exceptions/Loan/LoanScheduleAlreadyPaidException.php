<?php

namespace App\Exceptions\Loan;

use Exception;
use App\Models\LoanSchedule;

class LoanScheduleAlreadyPaidException extends Exception
{
    public function __construct(
        public LoanSchedule $schedule
    ) {
        parent::__construct('This loan schedule is already fully paid.');
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => [
                'schedule_id' => $this->schedule->id,
                'status' => 'PAID',
            ],
        ], 422);
    }
}
