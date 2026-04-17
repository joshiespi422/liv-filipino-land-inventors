<?php

namespace App\Services\Payments;

use App\Services\Payments\Gateways\PayMongoService;

class PaymentGatewayFactory
{
    /**
     * Create a new class instance.
     */
    public static function make(string $gateway)
    {
        return match ($gateway) {
            'paymongo' => app(PayMongoService::class),

            default => throw new \InvalidArgumentException("Unsupported gateway: {$gateway}"),
        };
    }
}
