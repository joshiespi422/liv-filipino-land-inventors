<?php

namespace App\Services\Membership;

use App\Models\MembershipSchedule;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Services\Payments\PaymentGatewayFactory;

class MembershipPaymentService
{
    public function initiate(MembershipSchedule $schedule, int $paymentMethodId): array
    {
        $method = PaymentMethod::findOrFail($paymentMethodId);
        $gateway = PaymentGatewayFactory::resolveGateway($method);
        $service = PaymentGatewayFactory::make($gateway);

        $intentResponse = $service->createPaymentIntent($schedule->amount / 100);
        $intentId = data_get($intentResponse, 'data.id');

        $methodResponse = $service->createPaymentMethod($method->gateway_type);
        $methodId = data_get($methodResponse, 'data.id');

        $attachResponse = $service->attach($intentId, $methodId);

        Payment::create([
            'payable_type' => MembershipSchedule::class,
            'payable_id' => $schedule->id,
            'payment_method_id' => $paymentMethodId,
            'status_id' => Status::PENDING,
            'payment_date' => now(),
            'amount' => $schedule->amount,
            'gateway' => $gateway,
            'gateway_payment_intent_id' => data_get($intentResponse, 'data.id'),
            'gateway_response' => $attachResponse,
        ]);

        return $service->getNextAction($attachResponse);
    }
}
