<?php

namespace App\Services\Loan;

use App\Exceptions\Loan\LoanInvalidPaymentAmountException;
use App\Exceptions\Loan\LoanScheduleAlreadyPaidException;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\LoanPayment;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class LoanPaymentService
{
    /**
     * Create a new class instance.
     */
    public function pay(Loan $loan, array $data)
    {
        return DB::transaction(function () use ($loan, $data) {

            $schedule = LoanSchedule::query()
                ->where('id', $data['loan_schedule_id'])
                ->where('loan_id', $loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            // prevent already paid
            if ($schedule->status_id === Status::PAID) {
                throw new LoanScheduleAlreadyPaidException($schedule);
            }

            // compute total paid (source of truth = DB)
            $paid = (float) LoanPayment::where('loan_schedule_id', $schedule->id)
                ->sum('amount');

            $total = (float) $schedule->total_payment;

            // remaining balance (SAFE)
            $remaining = round($total - $paid, 2);

            // prevent negative / already completed but not updated yet
            if ($remaining <= 0) {
                throw new LoanScheduleAlreadyPaidException($schedule);
            }

            // normalize to cents (NO FLOAT ERRORS)
            $amountCents = (int) round($data['amount'] * 100);
            $remainingCents = (int) round($remaining * 100);

            if ($amountCents !== $remainingCents) {
                throw new LoanInvalidPaymentAmountException(
                    required: $remaining,
                    provided: $data['amount']
                );
            }

            // create payment
            $payment = LoanPayment::create([
                'loan_id' => $loan->id,
                'loan_schedule_id' => $schedule->id,
                'payment_method_id' => $data['payment_method_id'],
                'payment_date' => now(),
                'amount' => $data['amount'],
            ]);

            // mark schedule paid
            $schedule->update([
                'status_id' => Status::PAID,
            ]);

            // check loan completion
            $hasUnpaid = LoanSchedule::where('loan_id', $loan->id)
                ->where('status_id', Status::UNPAID)
                ->exists();

            if (!$hasUnpaid) {
                $loan->update([
                    'status_id' => Status::FINISHED,
                ]);
            }

            // return clean payload
            return [
                'payment' => $payment,
                'schedule' => $schedule->fresh('loanPayments'),
                'loan' => $loan->fresh(['loanSchedules.loanPayments']),
            ];
        });
    }
}
