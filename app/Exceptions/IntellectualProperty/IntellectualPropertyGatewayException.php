<?php

namespace App\Exceptions\IntellectualProperty;

use Exception;
use Throwable;

class IntellectualPropertyGatewayException extends Exception
{
    public function __construct(
        string $message,
        public readonly mixed $gatewayErrors = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    public static function failedToCreatePaymentMethod(mixed $errors): self
    {
        return new self('Failed to create payment method with the gateway.', $errors);
    }

    public static function failedToCreatePaymentIntent(mixed $errors): self
    {
        return new self('Failed to create payment intent with the gateway.', $errors);
    }

    public static function missingClientSideMethodId(): self
    {
        return new self('Card payments require a gateway_payment_method_id from the client.');
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 502);
    }
}
