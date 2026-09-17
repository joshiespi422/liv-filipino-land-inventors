<?php

namespace App\Services\Transfer;

use App\Models\BatchTransfer;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransferService
{
    public const TRANSFER_FEE = 0.00;

    public const MIN_TRANSFER = 1.00;

    public const CHANNELS = [
        'gcash' => ['name' => 'GCash', 'search' => 'G-Xchange', 'provider' => 'instapay'],
        'maya' => ['name' => 'Maya', 'search' => 'Maya Philippines', 'provider' => 'instapay'],
        'aub' => ['name' => 'AUB', 'search' => 'Asia United Bank', 'provider' => 'instapay'],
        'bdo' => ['name' => 'BDO Unibank', 'search' => 'BDO Unibank', 'provider' => 'instapay'],
        'bpi' => ['name' => 'BPI', 'search' => 'Bank of the Philippine Islands', 'provider' => 'instapay'],
        'landbank' => ['name' => 'LandBank', 'search' => 'Land Bank of the Philippines', 'provider' => 'instapay'],
        'metrobank' => ['name' => 'Metrobank', 'search' => 'Metropolitan Bank', 'provider' => 'instapay'],
        'unionbank' => ['name' => 'UnionBank', 'search' => 'Union Bank of the Philippines', 'provider' => 'instapay'],
    ];

    /**
     * Maps a payment-operator GUID (sub-tag "00" inside a Merchant Account
     * Information block, tags 26-51) to the sub-tag that actually carries
     * the recipient's account/mobile number for that operator.
     *
     * IDs 26-51 are reserved for "any payment operator" per the EMVCo spec,
     * so each wallet/bank defines its own meaning for sub-tags 01-99 within
     * its own block — there is no universal "account number" sub-tag.
     *
     * Fill this in per real QR payload you capture. Use the debug logging
     * in resolveQr() below to find the GUID + correct sub-tag for each
     * wallet/bank your users actually scan (GCash, Maya, InstaPay banks
     * may all differ).
     */
    private const ACCOUNT_SUBTAG_BY_GUID = [
        'PH.INSTAPAY.ME' => '02', // proxy value (mobile number or account number)
    ];

    public function __construct(protected PaymongoTransferService $paymongo) {}

    public function transfer(User $user, array $data): BatchTransfer
    {
        $channelId = $data['channel_id'];
        $channel = self::CHANNELS[$channelId] ?? null;

        if (! $channel) {
            throw new DomainException('Unsupported destination channel.');
        }

        $amount = round((float) $data['amount'], 2);

        if ($amount < self::MIN_TRANSFER) {
            throw new DomainException('The minimum transfer amount is ₱'.number_format(self::MIN_TRANSFER, 2));
        }

        $totalDeduct = round($amount + self::TRANSFER_FEE, 2);

        $bic = $this->paymongo->resolveBic($channel['search']);
        if (! $bic) {
            throw new DomainException("Could not resolve routing details for {$channel['name']}. Please try again later.");
        }

        $referenceNumber = 'WD-'.Str::upper(Str::random(10));
        $sourceAccount = config('paymongo.source_account');
        $provider = $channel['provider'] ?? $data['provider'] ?? 'instapay';

        // 1. Lock Wallet and Deduct Funds
        $batchTransfer = DB::transaction(function () use ($user, $data, $channelId, $bic, $amount, $totalDeduct, $sourceAccount, $referenceNumber, $provider) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet) {
                throw new DomainException('Wallet not found.');
            }

            if ((float) $wallet->balance < $totalDeduct) {
                throw new DomainException('Insufficient wallet balance.');
            }

            $wallet->decrement('balance', $totalDeduct);

            $transfer = BatchTransfer::create([
                'wallet_id' => $wallet->id,
                'source_account_number' => $sourceAccount['number'] ?? null,
                'destination_account_number' => $data['account_number'],
                'destination_account_name' => $data['account_name'],
                'destination_account_bic' => $bic,
                'channel' => $channelId,
                'amount' => $amount,
                'fee' => self::TRANSFER_FEE,
                'provider' => $provider,
                'purpose' => $data['purpose'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'reference_number' => $referenceNumber,
                'status' => 'pending',
            ]);

            $transfer->walletTransaction()->create([
                'wallet_id' => $wallet->id,
                'amount' => $totalDeduct,
                'type' => 'debit',
                'description' => "Transfer to {$data['account_name']} ({$data['account_number']})",
            ]);

            return $transfer;
        });

        // 2. Dispatch PayMongo API
        $payload = [
            'provider' => $provider,
            'amount' => (int) round($amount * 100),
            'currency' => 'PHP',
            'purpose' => $data['purpose'] ?? 'Disbursement',
            'description' => $data['remarks'] ?? "Wallet withdrawal to {$channel['name']}",
            'reference_number' => $referenceNumber,
            'source_account' => [
                'number' => $sourceAccount['number'] ?? '',
                'name' => $sourceAccount['name'] ?? '',
                'bic' => $sourceAccount['bic'] ?? 'PAEYPHM2XXX',
            ],
            'destination_account' => [
                'number' => $data['account_number'],
                'name' => $data['account_name'],
                'bic' => $bic,
            ],
        ];

        if ($callbackUrl = config('paymongo.callback_url')) {
            $payload['callback_url'] = $callbackUrl;
        }

        try {
            $result = $this->paymongo->createBatchTransfer($payload);
            $transferData = $result['body']['data']['transfers'][0] ?? null;

            // Handled HTTP non-200 or payload error response from PayMongo
            if (! $result['ok'] || ! $transferData) {
                $errorMessage = $result['body']['errors'][0]['detail'] ?? 'Transfer could not be processed.';
                $failureCode = $result['body']['errors'][0]['code'] ?? 'api_error';

                $this->refundTransfer($batchTransfer, $failureCode, $result['body'] ?? []);

                throw new DomainException($errorMessage);
            }

            $batchTransfer->update([
                'paymongo_batch_id' => $result['body']['data']['id'] ?? null,
                'paymongo_transfer_id' => $transferData['id'] ?? null,
                'status' => $transferData['status'] ?? 'pending',
                'raw_response' => $result['body'] ?? [],
            ]);

            return $batchTransfer->fresh();

        } catch (Exception $e) {
            if ($e instanceof DomainException) {
                throw $e;
            }

            // Connection or timeout error: DO NOT refund immediately to avoid double spend if processed later.
            Log::error('Transfer API network error', [
                'reference' => $referenceNumber,
                'exception' => $e->getMessage(),
            ]);

            $batchTransfer->update([
                'status' => 'processing',
                'raw_response' => ['system_error' => $e->getMessage()],
            ]);

            throw new DomainException('Transfer request sent but confirmation is delayed. Check your transaction history shortly.');
        }
    }

    public function refundTransfer(BatchTransfer $transfer, string $failureCode, array $rawResponse = []): void
    {
        DB::transaction(function () use ($transfer, $failureCode, $rawResponse) {
            $transfer = BatchTransfer::whereKey($transfer->id)->lockForUpdate()->first();

            if (in_array($transfer->status, ['failed', 'returned', 'refunded'], true)) {
                return;
            }

            $wallet = Wallet::whereKey($transfer->wallet_id)->lockForUpdate()->first();

            if ($wallet) {
                $refundAmount = round($transfer->amount + $transfer->fee, 2);
                $wallet->increment('balance', $refundAmount);

                $transfer->walletTransaction()->create([
                    'wallet_id' => $wallet->id,
                    'amount' => $refundAmount,
                    'type' => 'credit',
                    'description' => "Refund: Failed transfer ({$transfer->reference_number})",
                ]);
            }

            $transfer->update([
                'status' => 'failed',
                'failure_code' => $failureCode,
                'raw_response' => array_merge($transfer->raw_response ?? [], $rawResponse),
            ]);
        });
    }

    public function resolveQr(string $qrPayload): array
    {
        $qrPayload = trim($qrPayload);

        // TEMP DEBUG — remove once ACCOUNT_SUBTAG_BY_GUID is filled in and confirmed.
        // Uncomment to log the raw payload of every scan so you can inspect
        // real GUIDs/sub-tags for wallets you haven't mapped yet.
        // Log::debug('QR payload received', ['payload' => $qrPayload]);

        // 1. Try parsing standard EMVCo / QR Ph string format
        if (str_starts_with($qrPayload, '000201')) {
            $result = $this->parseEmvCoQrPh($qrPayload);

            if (empty($result['account_number'])) {
                throw new DomainException('Could not identify the recipient account from this QR code. Please use a supported QR code or enter the recipient details manually.');
            }

            return $result;
        }

        // 2. Try parsing JSON format payload (if internal app generated QR)
        if (str_starts_with($qrPayload, '{') && str_ends_with($qrPayload, '}')) {
            $json = json_decode($qrPayload, true);
            if (is_array($json) && ! empty($json['account_number'])) {
                return [
                    'provider' => $json['provider'] ?? 'INTERNAL',
                    'account_name' => $json['account_name'] ?? '',
                    'account_number' => (string) $json['account_number'],
                    'amount' => isset($json['amount']) ? (float) $json['amount'] : null,
                    'qr_type' => $json['qr_type'] ?? 'STATIC',
                    'raw' => $qrPayload,
                ];
            }
        }

        // 3. Simple phone number or account string format
        if (preg_match('/^[0-9+]{10,15}$/', $qrPayload)) {
            return [
                'provider' => 'DIRECT',
                'account_name' => 'Recipient',
                'account_number' => $qrPayload,
                'amount' => null,
                'qr_type' => 'STATIC',
                'raw' => $qrPayload,
            ];
        }

        throw new DomainException('Invalid or unsupported QR code format.');
    }

    /**
     * Parse standard EMVCo Tag-Length-Value (TLV) payload.
     */
    private function parseEmvCoQrPh(string $payload): array
    {
        $tags = $this->tlvDecode($payload);

        $amount = isset($tags['54']) ? (float) $tags['54'] : null;
        $accountName = $tags['59'] ?? '';

        $accountNumber = '';
        $provider = 'QR Ph';

        for ($i = 26; $i <= 51; $i++) {
            $tagKey = sprintf('%02d', $i);

            if (! isset($tags[$tagKey])) {
                continue;
            }

            $subTags = $this->tlvDecode($tags[$tagKey]);
            $guid = $subTags['00'] ?? null;

            if ($guid === null) {
                continue;
            }

            $provider = $guid;
            $accountSubtag = self::ACCOUNT_SUBTAG_BY_GUID[$guid] ?? null;

            if ($accountSubtag !== null && isset($subTags[$accountSubtag])) {
                $accountNumber = $subTags[$accountSubtag];
                break;
            }

            // Unmapped GUID: fall back to picking the sub-tag whose value
            // looks like a phone number or account number (digits only,
            // optionally with a leading +, 7-20 chars). This lets unmapped
            // banks/wallets still resolve instead of hard failing, while we
            // gather real payloads to add exact mappings.
            $candidate = null;
            foreach ($subTags as $subKey => $subVal) {
                if ($subKey === '00') {
                    continue;
                }
                if (preg_match('/^\+?[0-9]{7,20}$/', $subVal)) {
                    $candidate = $subVal;
                    break;
                }
            }

            if ($candidate !== null) {
                Log::info('QR Ph: used heuristic fallback for unmapped GUID', [
                    'guid' => $guid,
                    'subtag_keys' => array_keys($subTags), // keys only, no values — avoids logging PII
                ]);
                $accountNumber = $candidate;
                break;
            }
        }

        // NOTE: removed the tag 62 / sub-tag 01 fallback — that field is
        // "Bill Number" per the EMVCo spec, not an account number, and
        // trusting it risked silently misrouting a transfer.

        return [
            'provider' => $provider,
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'amount' => $amount,
            'qr_type' => isset($tags['01']) && $tags['01'] === '12' ? 'DYNAMIC' : 'STATIC',
            'raw' => $payload,
        ];
    }

    /**
     * Decode a flat EMVCo Tag-Length-Value string into a [tag => value] map.
     * Used for both the top-level payload and nested sub-templates
     * (e.g. Merchant Account Information blocks).
     */
    private function tlvDecode(string $payload): array
    {
        $tags = [];
        $index = 0;
        $length = strlen($payload);

        while ($index < $length) {
            $tag = substr($payload, $index, 2);
            $tagLen = (int) substr($payload, $index + 2, 2);
            $value = substr($payload, $index + 4, $tagLen);
            $tags[$tag] = $value;
            $index += 4 + $tagLen;
        }

        return $tags;
    }

    private function extractSubTag(string $subPayload, string $targetSubTag): ?string
    {
        $index = 0;
        $length = strlen($subPayload);

        while ($index < $length) {
            $subTag = substr($subPayload, $index, 2);
            $subTagLength = (int) substr($subPayload, $index + 2, 2);
            $subValue = substr($subPayload, $index + 4, $subTagLength);

            if ($subTag === $targetSubTag) {
                return $subValue;
            }
            $index += 4 + $subTagLength;
        }

        return null;
    }
}
