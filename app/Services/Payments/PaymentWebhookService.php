<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookService
{
    private const SUCCESS_STATUSES = ['paid', 'succeeded'];

    private const FAILURE_STATUSES = ['failed', 'awaiting_payment_method', 'expired'];

    public function handle(
        string $gatewayPaymentIntentId,
        string $gatewayStatus,
        ?string $gatewayPaymentId,
        ?int $verifiedAmount = null,
    ): void {
        DB::transaction(function () use ($gatewayPaymentIntentId, $gatewayStatus, $gatewayPaymentId, $verifiedAmount) {
            $payment = Payment::where('gateway_payment_intent_id', $gatewayPaymentIntentId)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::warning('Webhook received for unknown payment intent', [
                    'intent_id' => $gatewayPaymentIntentId,
                ]);

                return;
            }

            if (! in_array($payment->status_id, [Status::PENDING, Status::WAITING_FOR_PAYMENT], true)) {
                Log::info('Ignoring webhook for already-finalized payment', [
                    'payment_id' => $payment->id,
                    'current_status_id' => $payment->status_id,
                ]);

                return;
            }

            if ($verifiedAmount !== null && (int) $verifiedAmount !== (int) $payment->amount) {
                Log::critical('Webhook amount mismatch — marking payment failed, investigate manually', [
                    'payment_id' => $payment->id,
                    'expected_amount' => $payment->amount,
                    'webhook_amount' => $verifiedAmount,
                ]);

                $payment->update([
                    'status_id' => Status::FAILED,
                    'gateway_payment_id' => $gatewayPaymentId,
                ]);

                return;
            }

            if (in_array($gatewayStatus, self::SUCCESS_STATUSES, true)) {
                $payment->update([
                    'status_id' => Status::PAID,
                    'gateway_payment_id' => $gatewayPaymentId,
                    'gateway_status' => $gatewayStatus,
                    'paid_at' => now(),
                ]);

                $payment->payable->onPaymentSuccess($payment);

                return;
            }

            if (in_array($gatewayStatus, self::FAILURE_STATUSES, true)) {
                $payment->update([
                    'status_id' => Status::FAILED,
                    'gateway_payment_id' => $gatewayPaymentId,
                    'gateway_status' => $gatewayStatus,
                ]);

                $payment->payable->onPaymentFailed($payment);

                return;
            }

            Log::info('Unhandled gateway status, ignoring', [
                'payment_id' => $payment->id,
                'gateway_status' => $gatewayStatus,
            ]);
        });
    }
}
