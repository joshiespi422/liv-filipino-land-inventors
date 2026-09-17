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

    public function __construct(protected PaymongoTransferService $paymongo) {}

    public function transfer(User $user, array $data): BatchTransfer
    {
        $channelId = $data['channel_id'];
        $channel = self::CHANNELS[$channelId] ?? null;

        if (! $channel) {
            throw new DomainException('Unsupported destination channel.');
        }

        $amount = round((float) $data['amount'], 2);

        // 1. Minimum Amount Check
        if ($amount < self::MIN_TRANSFER) {
            throw new DomainException('The minimum transfer amount is ₱'.number_format(self::MIN_TRANSFER, 2));
        }

        $totalDeduct = round($amount + self::TRANSFER_FEE, 2);

        // 2. Resolve Routing / BIC
        $bic = $this->paymongo->resolveBic($channel['search']);
        if (! $bic) {
            throw new DomainException("Could not resolve routing details for {$channel['name']}. Please try again later.");
        }

        $referenceNumber = 'WD-'.Str::upper(Str::random(10));
        $sourceAccount = config('paymongo.source_account');
        $provider = $channel['provider'] ?? $data['provider'] ?? 'instapay';

        // 3. Lock Wallet and Deduct Funds FIRST (Prevents double spending)
        $batchTransfer = DB::transaction(function () use ($user, $data, $channelId, $bic, $amount, $totalDeduct, $sourceAccount, $referenceNumber, $provider) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet) {
                throw new DomainException('Wallet not found.');
            }

            if ((float) $wallet->balance < $totalDeduct) {
                throw new DomainException('Insufficient wallet balance.');
            }

            // Deduct balance immediately
            $wallet->decrement('balance', $totalDeduct);

            // Record Pending Batch Transfer
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

            // Create Wallet Transaction Entry
            $transfer->walletTransaction()->create([
                'wallet_id' => $wallet->id,
                'amount' => $totalDeduct,
                'type' => 'debit',
                'description' => "Transfer to {$data['account_name']} ({$data['account_number']})",
            ]);

            return $transfer;
        });

        // 4. Construct Payload & Call PayMongo API safely
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

            if (! $result['ok'] || ! $transferData) {
                $errorMessage = $result['body']['errors'][0]['detail'] ?? 'Transfer could not be processed.';
                $failureCode = $result['body']['errors'][0]['code'] ?? 'api_error';

                // Refund the user wallet since the external API rejected it
                $this->refundTransfer($batchTransfer, $failureCode, $result['body'] ?? []);

                throw new DomainException($errorMessage);
            }

            // Update Batch Transfer with API Response details
            $batchTransfer->update([
                'paymongo_batch_id' => $result['body']['data']['id'] ?? null,
                'paymongo_transfer_id' => $transferData['id'] ?? null,
                'status' => $transferData['status'] ?? 'pending',
                'raw_response' => $result['body'] ?? [],
            ]);

            return $batchTransfer->fresh();

        } catch (Exception $e) {
            if (! ($e instanceof DomainException)) {
                // Unexpected API or HTTP failure: refund and mark failed
                $this->refundTransfer($batchTransfer, 'system_error', ['error' => $e->getMessage()]);
                Log::error('Transfer failed due to system exception', ['exception' => $e]);
                throw new DomainException('Service temporarily unavailable. Your balance has been restored.');
            }

            throw $e;
        }
    }

    /**
     * Helper to refund the locked funds back to the user wallet on API failure.
     */
    protected function refundTransfer(BatchTransfer $transfer, string $failureCode, array $rawResponse = []): void
    {
        DB::transaction(function () use ($transfer, $failureCode, $rawResponse) {
            $wallet = Wallet::whereKey($transfer->wallet_id)->lockForUpdate()->first();

            if ($wallet) {
                $wallet->increment('balance', $transfer->amount + $transfer->fee);

                $transfer->walletTransaction()->create([
                    'wallet_id' => $wallet->id,
                    'amount' => $transfer->amount + $transfer->fee,
                    'type' => 'credit',
                    'description' => "Refund: Failed transfer ({$transfer->reference_number})",
                ]);
            }

            $transfer->update([
                'status' => 'failed',
                'failure_code' => $failureCode,
                'raw_response' => $rawResponse,
            ]);
        });
    }
}
