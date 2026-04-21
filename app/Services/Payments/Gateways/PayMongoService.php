<?php

namespace App\Services\Payments\Gateways;

use App\Models\Status;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class PayMongoService implements PaymentGatewayInterface
{
    /**
     * Create a new class instance.
     */
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.paymongo.base_url');
        $this->secretKey = config('services.paymongo.secret_key');
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Create Payment Intent
     *
     * @param float $amount Amount in PHP
     * @return array
     */
    public function createPaymentIntent(
        float $amount,
        array $options = []
    ): array {
        $threeDS = $options['three_d_secure'] ?? 'automatic';
        $allowedMethods = ['card', 'paymaya', 'qrph', 'billease', 'grab_pay', 'dob'];

        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/payment_intents", [
                'data' => [
                    'attributes' => [
                        'amount' => (int) round($amount * 100),
                        'currency' => 'PHP',
                        'payment_method_allowed' => $allowedMethods,
                        'payment_method_options' => [
                            'card' => [
                                'request_three_d_secure' => $threeDS,
                            ],
                        ],
                        'capture_type' => 'automatic',
                    ],
                ],
            ])
            ->json();
    }

    /**
     * Create Payment Method
     *
     * @param string $type
     * @return array
     */
    public function createPaymentMethod(string $type): array
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/payment_methods", [
                'data' => [
                    'attributes' => [
                        'type' => $type,
                    ],
                ],
            ])
            ->json();
    }

    /**
     * Attach Payment Method to Intent
     *
     * @param string $intentId
     * @param string $methodId
     * @return array
     */
    public function attach(string $intentId, string $methodId): array
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/payment_intents/{$intentId}/attach", [
                'data' => [
                    'attributes' => [
                        'payment_method' => $methodId,
                        'return_url' => config('app.url') . 'api/payment/success',
                    ],
                ],
            ])
            ->json();
    }

    /**
     * Extract the next action details from the PayMongo response after attaching a payment method
     *
     * @param array $response
     * @return array{qr_code_url: mixed, redirect_url: mixed, status: mixed, type: mixed}
     */
    public function getNextAction(array $response): array
    {
        $attributes = data_get($response, 'data.attributes', []);
        $nextAction = data_get($attributes, 'next_action', []);
        $type = data_get($nextAction, 'type');

        return [
            'type' => $type,
            'redirect_url' => data_get($nextAction, 'redirect.url'),
            'qr_code_url' => match ($type) {
                'consume_qr' => data_get($nextAction, 'code.image_url')
                ?? data_get($nextAction, 'code.test_url'),
                default => null,
            },
            'status' => data_get($attributes, 'status'),
        ];
    }

    /**
     * Parse the webhook payload from PayMongo and return the relevant information
     *
     * @param array $payload
     * @return array{event: mixed, intent_id: null, status_id: null|array{event: string, gateway_payment_id: mixed, intent_id: mixed, status_id: int}}
     */
    public function parseWebhook(array $payload): array
    {
        $eventType = data_get($payload, 'data.attributes.type');

        $attr = data_get($payload, 'data.attributes.data.attributes', []);

        return [
            'intent_id' => $attr['payment_intent_id'] ?? null,
            'status' => $attr['status'] ?? null,
            'gateway_payment_id' => data_get($payload, 'data.attributes.data.id'),
            'event' => $eventType,
        ];
    }
}
