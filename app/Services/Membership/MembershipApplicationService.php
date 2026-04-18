<?php

namespace App\Services\Membership;

use App\Models\MemberMembership;
use App\Models\MembershipSchedule;
use App\Models\MembershipSetting;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MembershipApplicationService
{
    public function apply(User $user, int $termMonths): MemberMembership
    {
        $setting = MembershipSetting::current();

        if (!in_array($termMonths, $setting->allowed_term_months)) {
            throw new InvalidArgumentException("Term of {$termMonths} months is not allowed.");
        }

        return DB::transaction(function () use ($user, $termMonths, $setting) {
            $membership = MemberMembership::create([
                'user_id' => $user->id,
                'status_id' => Status::PENDING,
                'amount' => $setting->share_capital_amount,
                'term_months' => $termMonths,
            ]);

            $this->generateSchedules($membership);

            return $membership;
        });
    }

    private function generateSchedules(MemberMembership $membership): void
    {
        $total = $membership->amount;
        $months = $membership->term_months;
        $installment = (int) floor($total / $months);
        $remainder = $total - ($installment * $months);

        $schedules = [];

        for ($i = 1; $i <= $months; $i++) {
            $schedules[] = [
                'member_membership_id' => $membership->id,
                'status_id' => Status::UNPAID,
                'installment_no' => $i,
                // remainder goes to last installment
                'amount' => $i === $months
                    ? $installment + $remainder
                    : $installment,
                'due_date' => now()->addMonths($i)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        MembershipSchedule::insert($schedules);
    }
}
