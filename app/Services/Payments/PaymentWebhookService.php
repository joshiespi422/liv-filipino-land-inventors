<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\LoanSchedule;
use App\Models\PaymentGatewayLog;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookService
{
    /**
     * Create a new class instance.
     */
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
            PaymentGatewayLog::create([
                'payment_id' => null,
                'gateway' => 'paymongo',
                'event' => 'unknown_payment_intent',
                'payload' => [
                    'intent_id' => $gatewayPaymentIntentId,
                    'status' => $gatewayStatus,
                ],
            ]);
            return;
        }

        // Idempotency guard
        if ((int) $payment->status_id === Status::SUCCESS) {
            Log::info('Webhook already processed.', ['payment_id' => $payment->id]);
            PaymentGatewayLog::create([
                'payment_id' => $payment->id,
                'gateway' => $payment->gateway ?? 'paymongo',
                'event' => 'already_processed',
                'payload' => [],
            ]);
            return;
        }

        $payable = $payment->payable;

        if (!$payable instanceof LoanSchedule) {
            Log::warning('Payment not linked to LoanSchedule.', [
                'payment_id' => $payment->id,
                'type' => $payment->payable_type,
            ]);
            PaymentGatewayLog::create([
                'payment_id' => $payment->id,
                'gateway' => $payment->gateway ?? 'paymongo',
                'event' => 'invalid_payable',
                'payload' => ['type' => $payment->payable_type],
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

                PaymentGatewayLog::create([
                    'payment_id' => $payment->id,
                    'gateway' => $payment->gateway ?? 'paymongo',
                    'event' => 'payment_success',
                    'payload' => [
                        'intent_id' => $payment->gateway_payment_intent_id,
                        'gateway_payment_id' => $gatewayPaymentId,
                    ],
                ]);

                $this->tryFinishLoan($payable);
            });
        }

        if ($gatewayStatus === Status::FAILED) {
            $payment->update(['status_id' => Status::FAILED]);

            PaymentGatewayLog::create([
                'payment_id' => $payment->id,
                'gateway' => $payment->gateway ?? 'paymongo',
                'event' => 'payment_failed',
                'payload' => [
                    'intent_id' => $gatewayPaymentIntentId,
                ],
            ]);

            Log::info('Payment marked as failed.', ['payment_id' => $payment->id]);
        }
    }

    private function tryFinishLoan(LoanSchedule $schedule): void
    {
        $loan = $schedule->loan;

        if (!$loan) {
            Log::error('LoanSchedule has no loan.', ['schedule_id' => $schedule->id]);
            PaymentGatewayLog::create([
                'payment_id' => null,
                'gateway' => 'system',
                'event' => 'missing_loan_relation',
                'payload' => ['schedule_id' => $schedule->id],
            ]);
            return;
        }

        $hasUnpaid = LoanSchedule::where('loan_id', $loan->id)
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if (!$hasUnpaid) {
            $loan->update(['status_id' => Status::FINISHED]);

            Log::info('Loan marked as finished.', ['loan_id' => $loan->id]);
            PaymentGatewayLog::create([
                'payment_id' => null,
                'gateway' => 'system',
                'event' => 'loan_finished',
                'payload' => ['loan_id' => $loan->id],
            ]);
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
