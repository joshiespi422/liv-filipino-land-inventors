<?php

namespace App\Services\Payments\Contracts;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createPaymentIntent(float $amount, array $options = []): array;

    public function createPaymentMethod(string $type): array;

    public function attach(string $intentId, string $methodId): array;

    public function getNextAction(array $response): array;

    public function parseWebhook(array $payload): array;

    public function verifyWebhookSignature(Request $request): bool;
}
