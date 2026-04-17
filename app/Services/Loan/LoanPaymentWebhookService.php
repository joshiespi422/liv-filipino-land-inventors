<?php

namespace App\Services\Loan;

use App\Models\Payment;
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

        $payment = Payment::where('gateway_payment_intent_id', $gatewayPaymentIntentId)->first();

        if (!$payment) {
            Log::warning('Webhook received for unknown payment intent.', [
                'intent_id' => $gatewayPaymentIntentId,
            ]);
            return;
        }

        // Idempotency guard
        if ((int) $payment->status_id === Status::SUCCESS) {
            Log::info('Webhook already processed.', ['payment_id' => $payment->id]);
            return;
        }

        $payable = $payment->payable;

        if (!$payable instanceof LoanSchedule) {
            Log::warning('Payment not linked to LoanSchedule.', [
                'payment_id' => $payment->id,
                'type' => $payment->payable_type,
            ]);
            return;
        }

        if ($gatewayStatus === Status::SUCCESS || $gatewayStatus === 'paid') {
            DB::transaction(function () use ($payment, $payable, $gatewayPaymentId) {
                $payment->update([
                    'status_id' => Status::SUCCESS,
                    'gateway_payment_id' => $gatewayPaymentId,
                ]);

                // mark schedule as paid
                $payable->update(['status_id' => Status::PAID]);

                $this->tryFinishLoan($payable);
            });
        }

        if ($gatewayStatus === Status::FAILED) {
            $payment->update(['status_id' => Status::FAILED]);

            Log::info('Payment marked as failed.', ['payment_id' => $payment->id]);
        }
    }

    private function tryFinishLoan(LoanSchedule $schedule): void
    {
        $loan = $schedule->loan;

        if (!$loan) {
            Log::error('LoanSchedule has no loan.', ['schedule_id' => $schedule->id]);
            return;
        }

        $hasUnpaid = LoanSchedule::where('loan_id', $loan->id)
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if (!$hasUnpaid) {
            $loan->update(['status_id' => Status::FINISHED]);

            Log::info('Loan marked as finished.', ['loan_id' => $loan->id]);
        }
    }

    private function normalizeStatus(string $status): string|int
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
