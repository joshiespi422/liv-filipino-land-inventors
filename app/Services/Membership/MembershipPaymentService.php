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
use Throwable;

class MembershipPaymentService
{
    public function initiate(MembershipSchedule $schedule, int $paymentMethodId): array
    {
        $this->validateSchedule($schedule);

        $method = PaymentMethod::findOrFail($paymentMethodId);
        $gateway = PaymentGatewayFactory::resolveGateway($method);
        $service = PaymentGatewayFactory::make($gateway);

        try {
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
            ]);

            return [
                'payment' => $payment,
                'next_action' => $service->getNextAction($attached),
            ];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function validateSchedule(MembershipSchedule $schedule): void
    {
        if ($schedule->status_id === Status::PAID) {
            throw new MembershipScheduleAlreadyPaidException($schedule);
        }

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
