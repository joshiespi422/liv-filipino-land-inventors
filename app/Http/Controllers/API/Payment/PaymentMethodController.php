<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Payment\ApiPaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $paymentMethods = PaymentMethod::all();

        return ApiPaymentMethodResource::collection($paymentMethods);
    }
}
