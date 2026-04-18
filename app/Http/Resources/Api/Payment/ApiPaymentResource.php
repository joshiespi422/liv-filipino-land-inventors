<?php

namespace App\Http\Resources\Api\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiPaymentResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'amount',
        'payment_date',
        'payment_method',
        'gateway',
        'status',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'amount' => number_format($this->amount / 100, 2, '.', ''), // cents → PHP
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'payment_method' => $this->paymentMethod?->name ?? null,
            'gateway' => $this->gateway,
            'status' => $this->status?->name ?? 'Unknown',
            'next_action' => $this->whenNotNull(
                $this->gateway_response
                ? $this->resolveNextAction()
                : null
            ),
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [];

    private function resolveNextAction(): ?array
    {
        $response = $this->gateway_response;
        $attributes = data_get($response, 'data.attributes', []);
        $nextAction = data_get($attributes, 'next_action', []);
        $type = data_get($nextAction, 'type');

        if (!$type) {
            return null;
        }

        return [
            'type' => $type,
            'redirect_url' => data_get($nextAction, 'redirect.url'),
            'qr_code_url' => match ($type) {
                'consume_qr' => data_get($nextAction, 'code.image_url')
                ?? data_get($nextAction, 'code.test_url'),
                default => null,
            },
        ];
    }

}
