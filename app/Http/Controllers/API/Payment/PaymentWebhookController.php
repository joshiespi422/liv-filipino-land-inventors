<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayLog;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentWebhookService $webhookService,
    ) {}

    public function __invoke(Request $request, string $gateway): JsonResponse
    {
        if (! in_array($gateway, PaymentGatewayFactory::SUPPORTED_GATEWAYS, true)) {
            abort(404);
        }

        $service = PaymentGatewayFactory::make($gateway);

        if (! $service->verifyWebhookSignature($request)) {
            Log::warning('Rejected wallet/load webhook: invalid signature', ['gateway' => $gateway]);

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->all();
        $eventId = data_get($payload, 'data.id');

        try {
            PaymentGatewayLog::create([
                'payment_id' => null,
                'gateway' => $gateway,
                'gateway_event_id' => $eventId,
                'event' => 'webhook_received',
                'payload' => $payload,
            ]);
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                Log::info('Duplicate webhook event ignored', ['gateway' => $gateway, 'event_id' => $eventId]);

                return response()->json(['ok' => true, 'duplicate' => true], 200);
            }

            throw $e;
        }

        $data = $service->parseWebhook($payload);

        if (empty($data['intent_id']) || empty($data['status'])) {
            PaymentGatewayLog::create([
                'payment_id' => null,
                'gateway' => $gateway,
                'event' => 'invalid_payload',
                'payload' => $payload,
            ]);

            return response()->json(['message' => 'Invalid webhook payload'], 200);
        }

        $this->webhookService->handle(
            gatewayPaymentIntentId: $data['intent_id'],
            gatewayStatus: $data['status'],
            gatewayPaymentId: $data['gateway_payment_id'],
            verifiedAmount: $data['amount'] ?? null,
        );

        return response()->json(['ok' => true], 200);
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return $e->getCode() === '23000';
    }
}
