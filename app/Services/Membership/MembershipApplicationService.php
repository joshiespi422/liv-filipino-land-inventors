<?php

namespace App\Services\Membership;

use App\Exceptions\Membership\MembershipAlreadyExistsException;
use App\Exceptions\Membership\MembershipCancellationException;
use App\Exceptions\Membership\MembershipInvalidTermException;
use App\Models\MemberMembership;
use App\Models\MembershipSchedule;
use App\Models\MembershipSetting;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class MembershipApplicationService
{
    public function apply(User $user, int $termMonths): MemberMembership
    {
        $setting = MembershipSetting::current();

        if (!in_array($termMonths, $setting->allowed_term_months)) {
            throw new MembershipInvalidTermException($termMonths, $setting->allowed_term_months);
        }

        $this->ensureNoActiveMembership($user);

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

    public function cancel(User $user): void
    {
        $membership = MemberMembership::where('user_id', $user->id)
            ->whereIn('status_id', [Status::PENDING, Status::ACTIVE])
            ->first();

        if (!$membership) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'No active or pending membership found to cancel.',
                ], 404)
            );
        }

        if ($membership->status_id === Status::CANCELLED) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Membership is already cancelled.',
                ], 422)
            );
        }

        $hasPendingPayment = $membership->schedules()
            ->whereHas('payments', fn($q) => $q->where('status_id', Status::PENDING))
            ->exists();

        if ($hasPendingPayment) {
            throw new MembershipCancellationException(
                'Cannot cancel while a payment is being processed.'
            );
        }

        $membership->update(['status_id' => Status::CANCELLED]);
        $membership->schedules()->update(['status_id' => Status::CANCELLED]);
    }

    private function ensureNoActiveMembership(User $user): void
    {
        $existing = MemberMembership::where('user_id', $user->id)
            ->whereIn('status_id', [Status::PENDING, Status::ACTIVE])
            ->first();

        if (!$existing) {
            return;
        }

        if ($existing->status_id === Status::ACTIVE) {
            throw new MembershipAlreadyExistsException('You already have an active membership.');
        }

        // PENDING — check if all payments failed (stuck), auto-cancel so they can re-apply
        $hasNonFailedPayment = $existing->schedules()
            ->whereHas('payments', fn($q) => $q->whereNotIn('status_id', [Status::FAILED]))
            ->exists();

        if ($hasNonFailedPayment) {
            throw new MembershipAlreadyExistsException('You already have a pending membership.');
        }

        // Cancel both membership and its schedules
        $existing->update(['status_id' => Status::CANCELLED]);
        $existing->schedules()->update(['status_id' => Status::CANCELLED]);
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
