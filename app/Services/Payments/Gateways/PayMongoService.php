<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayMongoService implements PaymentGatewayInterface
{
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
            'Authorization' => 'Basic '.base64_encode($this->secretKey.':'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function createPaymentIntent(
        float $amount,
        array $options = []
    ): array {
        $threeDS = $options['three_d_secure'] ?? 'automatic';
        $allowedMethods = ['card', 'paymaya', 'qrph', 'billease', 'grab_pay', 'dob'];

        $headers = $this->headers();

        if (! empty($options['idempotency_key'])) {
            $headers['Idempotency-Key'] = $options['idempotency_key'];
        }

        $attributes = [
            'amount' => (int) round($amount * 100),
            'currency' => 'PHP',
            'payment_method_allowed' => $allowedMethods,
            'payment_method_options' => [
                'card' => [
                    'request_three_d_secure' => $threeDS,
                ],
            ],
            'capture_type' => 'automatic',
        ];

        if (! empty($options['description'])) {
            $attributes['description'] = $options['description'];
        }

        return Http::withHeaders($headers)
            ->post("{$this->baseUrl}/payment_intents", [
                'data' => [
                    'attributes' => $attributes,
                ],
            ])
            ->json();
    }

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

    public function attach(string $intentId, string $methodId): array
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/payment_intents/{$intentId}/attach", [
                'data' => [
                    'attributes' => [
                        'payment_method' => $methodId,
                        'return_url' => config('app.url').'api/payment/success',
                    ],
                ],
            ])
            ->json();
    }

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

    public function parseWebhook(array $payload): array
    {
        $eventType = data_get($payload, 'data.attributes.type');

        $attr = data_get($payload, 'data.attributes.data.attributes', []);

        return [
            'intent_id' => $attr['payment_intent_id'] ?? null,
            'status' => $attr['status'] ?? null,
            'gateway_payment_id' => data_get($payload, 'data.attributes.data.id'),
            'amount' => $attr['amount'] ?? null,
            'event' => $eventType,
        ];
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = config('services.paymongo.webhook_secret');
        $header = $request->header('Paymongo-Signature');

        if (! $secret || ! $header) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[trim((string) $key)] = $value !== null ? trim($value) : null;
        }

        if (empty($parts['t'])) {
            return false;
        }

        if (abs(time() - (int) $parts['t']) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$parts['t']}.{$request->getContent()}", $secret);

        $liveOrTest = app()->environment('production') ? 'li' : 'te';
        $given = $parts[$liveOrTest] ?? null;

        return $given && hash_equals($expected, $given);
    }
}
