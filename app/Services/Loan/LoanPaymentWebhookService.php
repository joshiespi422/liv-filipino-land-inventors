<?php

namespace App\Services\Loan;

use App\Models\LoanPayment;
use App\Models\LoanSchedule;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanPaymentWebhookService
{
    public function handle(
        string $gatewayPaymentIntentId,
        string $gatewayStatus,
        ?string $gatewayPaymentId = null
    ): void {
        $gatewayStatus = $this->normalizeStatus($gatewayStatus);

        $payment = LoanPayment::where('gateway_payment_intent_id', $gatewayPaymentIntentId)->first();

        if (!$payment) {
            Log::warning('Webhook received for unknown payment intent.', [
                'intent_id' => $gatewayPaymentIntentId,
            ]);
            return;
        }

        // Idempotency guard
        if ($payment->status_id === Status::SUCCESS) {
            Log::info('Webhook already processed for payment.', ['payment_id' => $payment->id]);
            return;
        }

        if ($gatewayStatus === Status::SUCCESS || $gatewayStatus === 'paid') {
            DB::transaction(function () use ($payment, $gatewayPaymentId) {
                $payment->update([
                    'status_id' => Status::SUCCESS,
                    'gateway_payment_id' => $gatewayPaymentId,
                ]);

                $schedule = $payment->loanSchedule;
                $schedule->update(['status_id' => Status::PAID]);

                $this->tryFinishLoan($schedule);
            });
        }

        if ($gatewayStatus === Status::FAILED) {
            $payment->update(['status_id' => Status::FAILED]);

            Log::info('Webhook marked payment as failed.', ['payment_id' => $payment->id]);
        }
    }

    private function tryFinishLoan(LoanSchedule $schedule): void
    {
        $loan = $schedule->loan;

        if (!$loan) {
            Log::error('LoanSchedule has no associated loan.', ['schedule_id' => $schedule->id]);
            return;
        }

        $hasUnpaid = LoanSchedule::where('loan_id', $loan->id)
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if (!$hasUnpaid) {
            $loan->update(['status_id' => Status::FINISHED]);

            Log::info('Loan automatically marked as finished.', ['loan_id' => $loan->id]);
        }
    }

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'paid',
            'payment.paid',
            'success',
            'succeeded' => Status::SUCCESS,

            'failed',
            'payment.failed' => Status::FAILED,

            default => $status,
        };
    }
}
