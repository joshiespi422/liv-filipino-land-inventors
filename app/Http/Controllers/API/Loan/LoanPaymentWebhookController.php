<?php

namespace App\Http\Controllers\API\Loan;

use App\Http\Controllers\Controller;
use App\Models\LoanPayment;
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

        if (
            empty($data['intent_id']) ||
            empty($data['status'])
        ) {
            return response()->json([
                'message' => 'Invalid webhook payload'
            ], 400);
        }

        $this->webhookService->handle(
            gatewayPaymentIntentId: $data['intent_id'],
            gatewayStatus: $data['status'],
            gatewayPaymentId: $data['gateway_payment_id'] ?? null,
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Mobile will call this to check status
     */
    public function status(string $paymentIntentId): JsonResponse
    {
        $payment = LoanPayment::where('payment_intent_id', $paymentIntentId)->first();

        if (!$payment) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'status' => $payment->status, // pending | paid | failed
            'payment_intent_id' => $payment->payment_intent_id,
            'paid_at' => $payment->paid_at,
        ]);
    }

    /**
     * Optional fallback endpoint (NO BUSINESS LOGIC HERE)
     */
    public function success(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Payment redirect received (for UI only)',
            'payment_intent_id' => $request->get('payment_intent_id'),
        ]);
    }
}
