<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LoanPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
