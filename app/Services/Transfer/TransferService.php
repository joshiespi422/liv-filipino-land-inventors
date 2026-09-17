<?php

namespace App\Http\Controllers\API\Transfer;

use App\Http\Controllers\Controller;
use App\Models\BatchTransfer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymongoTransferWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $secret = config('paymongo.webhook_secret');
        $signatureHeader = $request->header('Paymongo-Signature');

        if (! $secret || ! $signatureHeader || ! $this->isValidSignature($request->getContent(), $signatureHeader, $secret)) {
            Log::warning('Rejected PayMongo transfer webhook: invalid signature.');

            return response('Invalid signature', 400);
        }

        $payload = $request->json()->all();
        Log::info('PayMongo transfer webhook received', $payload);

        // Flexible extraction for both wrapped webhook object and direct object payloads
        $eventData = $payload['data']['attributes']['data'] ?? $payload['data'] ?? [];
        $transferId = $eventData['id'] ?? null;
        $status = $eventData['attributes']['status'] ?? $eventData['status'] ?? null;

        if (! $transferId || ! $status) {
            return response('Ignored', 200);
        }

        $batchTransfer = BatchTransfer::where('paymongo_transfer_id', $transferId)->first();

        if (! $batchTransfer) {
            return response('No matching transfer record', 200);
        }

        DB::transaction(function () use ($batchTransfer, $status, $payload) {
            $transfer = BatchTransfer::whereKey($batchTransfer->id)
                ->lockForUpdate()
                ->first();

            if (! $transfer || $transfer->status === $status) {
                return;
            }

            $isFailure = in_array($status, ['failed', 'returned'], true);
            $alreadyRefunded = in_array($transfer->status, ['failed', 'returned', 'refunded'], true);

            $transfer->update([
                'status' => $status,
                'raw_response' => array_merge($transfer->raw_response ?? [], ['webhook' => $payload]),
            ]);

            if ($isFailure && ! $alreadyRefunded) {
                $wallet = $transfer->wallet()->lockForUpdate()->first();

                if ($wallet) {
                    $refund = round($transfer->amount + $transfer->fee, 2);
                    $wallet->increment('balance', $refund);

                    $transfer->walletTransaction()->create([
                        'wallet_id' => $wallet->id,
                        'amount' => $refund,
                        'type' => 'credit',
                        'description' => "Refund for failed transfer {$transfer->reference_number}",
                    ]);
                }
            }
        });

        return response('OK', 200);
    }

    protected function isValidSignature(string $payload, string $header, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[trim((string) $key)] = $value !== null ? trim($value) : null;
        }

        if (empty($parts['t'])) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$parts['t']}.{$payload}", $secret);

        foreach (array_filter([$parts['li'] ?? null, $parts['te'] ?? null]) as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }
}
