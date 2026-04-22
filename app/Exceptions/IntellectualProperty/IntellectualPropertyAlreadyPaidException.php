<?php

namespace App\Exceptions\IntellectualProperty;

use App\Models\IntellectualPropertySchedule;
use Exception;

class IntellectualPropertyAlreadyPaidException extends Exception
{
    public function __construct(
        public readonly IntellectualPropertySchedule $schedule,
    ) {
        parent::__construct('This intellectual property schedule is already fully paid.');
    }

    public function render(): \Illuminate\Http\JsonResponse
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
