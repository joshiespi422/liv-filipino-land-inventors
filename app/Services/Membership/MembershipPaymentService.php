<?php

namespace App\Services\Membership;

use App\Exceptions\Membership\MembershipGatewayException;
use App\Exceptions\Membership\MembershipPendingPaymentExistsException;
use App\Exceptions\Membership\MembershipScheduleAlreadyPaidException;
use App\Models\MembershipSchedule;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MembershipPaymentService
{
    public function initiate(MembershipSchedule $schedule, int $paymentMethodId): array
    {
        return DB::transaction(function () use ($schedule, $paymentMethodId) {

            $schedule = MembershipSchedule::where('id', $schedule->id)
                ->lockForUpdate()
                ->with('membership')
                ->firstOrFail();

            // Clean old attempts
            $schedule->payments()
                ->whereIn('status_id', [Status::FAILED, Status::CANCELLED])
                ->update(['status_id' => Status::ARCHIVED]);

            $this->validateSchedule($schedule);

            $method = PaymentMethod::findOrFail($paymentMethodId);
            $gateway = PaymentGatewayFactory::resolveGateway($method);
            $service = PaymentGatewayFactory::make($gateway);

            $gatewayMethodId = $this->resolveGatewayMethodId($service, $method, []);

            $intentResponse = $service->createPaymentIntent($schedule->amount / 100);

            $intentId = data_get($intentResponse, 'data.id')
                ?? throw MembershipGatewayException::failedToCreatePaymentIntent(
                    data_get($intentResponse, 'errors')
                );

            $attached = $service->attach($intentId, $gatewayMethodId);

            $payment = $schedule->payments()->create([
                'payment_method_id' => $paymentMethodId,
                'status_id' => Status::PENDING,
                'payment_date' => now(),
                'amount' => $schedule->amount,
                'gateway' => $gateway,
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
                'gateway_status' => data_get($attached, 'data.attributes.status'),
                'idempotency_key' => Str::uuid(),
            ]);

            return [
                'payment' => $payment,
                'next_action' => $service->getNextAction($attached),
            ];
        });
    }

    private function validateSchedule(MembershipSchedule $schedule): void
    {
        // Block cancelled schedules
        if ($schedule->status_id === Status::CANCELLED) {
            throw new \RuntimeException('Cannot pay a cancelled schedule.');
        }

        // Block if parent membership is cancelled
        if ($schedule->membership->status_id === Status::CANCELLED) {
            throw new \RuntimeException('Cannot pay a schedule for a cancelled membership.');
        }

        // Block if parent membership is not yet approved/active
        if (!in_array($schedule->membership->status_id, [Status::ACTIVE, Status::APPROVED])) {
            throw new \RuntimeException('Membership is not in a payable state.');
        }

        // Block already paid schedule
        if ($schedule->status_id === Status::PAID) {
            throw new MembershipScheduleAlreadyPaidException($schedule);
        }

        // Block in-flight payment
        if ($schedule->payments()->where('status_id', Status::PENDING)->exists()) {
            throw new MembershipPendingPaymentExistsException($schedule);
        }
    }

    private function resolveGatewayMethodId($gateway, PaymentMethod $method, array $data): string
    {
        if ($method->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw MembershipGatewayException::missingClientSideMethodId();
        }

        $response = $gateway->createPaymentMethod($method->gateway_type);

        return data_get($response, 'data.id')
            ?? throw MembershipGatewayException::failedToCreatePaymentMethod(
                data_get($response, 'errors')
            );
    }
}
