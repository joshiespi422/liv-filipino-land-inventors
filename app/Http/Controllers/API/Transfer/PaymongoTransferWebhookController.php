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

        $transferId = $payload['data']['attributes']['data']['id'] ?? null;
        $status = $payload['data']['attributes']['data']['attributes']['status'] ?? null;

        if (! $transferId || ! $status) {
            return response('Ignored', 200);
        }

        $exists = BatchTransfer::where('paymongo_transfer_id', $transferId)->exists();

        if (! $exists) {
            return response('No change', 200);
        }

        DB::transaction(function () use ($transferId, $status) {
            $batchTransfer = BatchTransfer::where('paymongo_transfer_id', $transferId)
                ->lockForUpdate()
                ->first();

            if (! $batchTransfer || $batchTransfer->status === $status) {
                return;
            }

            $isFailure = in_array($status, ['failed', 'returned'], true);
            $alreadyFinal = in_array($batchTransfer->status, ['failed', 'returned', 'refunded'], true);

            $batchTransfer->update(['status' => $status]);

            if ($isFailure && ! $alreadyFinal) {
                $wallet = $batchTransfer->wallet()->lockForUpdate()->first();

                if ($wallet) {
                    $refund = $batchTransfer->amount + $batchTransfer->fee;
                    $wallet->increment('balance', $refund);

                    $batchTransfer->walletTransaction()->create([
                        'wallet_id' => $wallet->id,
                        'amount' => $refund,
                        'type' => 'credit',
                        'description' => "Refund for failed transfer {$batchTransfer->reference_number}",
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
