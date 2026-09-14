<?php

declare(strict_types=1);

namespace App\Services\Movider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class MoviderVerifyService
{
    private readonly ?string $key;

    private readonly ?string $secret;

    private readonly ?string $from;

    private readonly Client $client;

    public function __construct()
    {
        $this->key = config('services.movider.key');
        $this->secret = config('services.movider.secret');
        $this->from = config('services.movider.sender', 'BB88');

        $this->client = new Client([
            'base_uri' => 'https://api.movider.co/v1/',
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/x-www-form-urlencoded',
            ],
        ]);
    }

    /**
     * Normalize a PH phone number to E.164 format (+63XXXXXXXXXX).
     */
    private function toE164(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '63')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+63'.substr($digits, 1);
        }

        return '+63'.$digits;
    }

    /**
     * Start verification (send OTP to phone).
     */
    public function startVerification(string $phone, int $codeLength = 6, string $language = 'en-us', int $expire = 300): ?array
    {
        $formattedPhone = $this->toE164($phone);

        try {
            $response = $this->client->post('verify', [
                'form_params' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                    'to' => $formattedPhone,
                    'code_length' => $codeLength,
                    'from' => $this->from,
                    'language' => $language,
                    'pin_expire' => $expire,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);

            Log::error('Movider startVerification failed', [
                'original_phone' => $phone,
                'formatted_phone' => $formattedPhone,
                'status' => $e->getResponse()->getStatusCode(),
                'response' => $body,
            ]);

            return $body;
        }
    }

    /**
     * Verify code entered by user.
     */
    public function acknowledge(string $requestId, string $code): ?array
    {
        try {
            $response = $this->client->post('verify/acknowledge', [
                'form_params' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                    'request_id' => $requestId,
                    'code' => $code,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);

            Log::error('Movider acknowledge failed', [
                'request_id' => $requestId,
                'status' => $e->getResponse()->getStatusCode(),
                'response' => $body,
            ]);

            return $body;
        }
    }

    /**
     * Cancel verification request.
     */
    public function cancel(string $requestId): ?array
    {
        try {
            $response = $this->client->post('verify/cancel', [
                'form_params' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                    'request_id' => $requestId,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);

            Log::error('Movider cancel failed', [
                'request_id' => $requestId,
                'status' => $e->getResponse()->getStatusCode(),
                'response' => $body,
            ]);

            return $body;
        }
    }
}
