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
                'status' => 'not_found'
            ], 404);
        }

        return response()->json([
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
            'payment_intent_id' => $payment->payment_intent_id,
        ]);
    }

    /**
     * Optional fallback endpoint (NO BUSINESS LOGIC HERE)
     */
    public function success(): JsonResponse
    {
        return response()->json([
            'message' => 'Redirect received (UI only)'
        ]);
    }
}
