<?php

namespace App\Exceptions\Membership;

use App\Models\MembershipSchedule;
use Exception;

class MembershipScheduleAlreadyPaidException extends Exception
{
    public function __construct(
        public readonly MembershipSchedule $schedule,
    ) {
        parent::__construct('This membership schedule is already fully paid.');
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
