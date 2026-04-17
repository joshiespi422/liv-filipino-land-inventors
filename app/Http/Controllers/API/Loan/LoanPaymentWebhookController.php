<?php

namespace App\Http\Controllers\API\Loan;

use App\Http\Controllers\Controller;
use App\Services\Loan\LoanPaymentWebhookService;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanPaymentWebhookController extends Controller
{
    public function __construct(
        private readonly LoanPaymentWebhookService $webhookService,
    ) {
    }

    public function __invoke(Request $request, string $gateway): JsonResponse
    {
        $service = PaymentGatewayFactory::make($gateway);

        // Verify the request actually came from the gateway
        // if (! $service->verifyWebhookSignature($request)) {
        //     return response()->json(['message' => 'Invalid signature.'], 401);
        // }

        $data = $service->parseWebhook($request->all());

        if (empty($data['intent_id']) || empty($data['status'])) {
            return response()->json([
                'message' => 'Invalid webhook payload'
            ], 200);
        }

        $this->webhookService->handle(
            gatewayPaymentIntentId: $data['intent_id'],
            gatewayStatus: $data['status'],
            gatewayPaymentId: $data['gateway_payment_id'],
        );

        return response()->json(['ok' => true], 200);
    }
}
