<?php

namespace App\Services\Transfer;

use App\Models\BatchTransfer;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransferService
{
    public const TRANSFER_FEE = 0.00;

    public const MIN_TRANSFER = 1.00;

    public const CHANNELS = [
        'gcash' => ['name' => 'GCash', 'search' => 'G-Xchange'],
        'maya' => ['name' => 'Maya', 'search' => 'Maya Philippines'],
        'bdo' => ['name' => 'BDO Unibank', 'search' => 'BDO Unibank'],
        'bpi' => ['name' => 'BPI', 'search' => 'Bank of the Philippine Islands'],
        'landbank' => ['name' => 'LandBank', 'search' => 'Land Bank of the Philippines'],
        'metrobank' => ['name' => 'Metrobank', 'search' => 'Metropolitan Bank'],
        'unionbank' => ['name' => 'UnionBank', 'search' => 'Union Bank of the Philippines'],
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
        $totalDeduct = round($amount + self::TRANSFER_FEE, 2);

        // Pre-check lock & balance before making outbound API calls
        $wallet = $user->wallet;
        if (! $wallet) {
            throw new DomainException('Wallet not found.');
        }

        if ((float) $wallet->balance < $totalDeduct) {
            throw new DomainException('Insufficient wallet balance.');
        }

        $bic = $this->paymongo->resolveBic($channel['search']);
        if (! $bic) {
            throw new DomainException("Could not resolve routing details for {$channel['name']}. Please try again later.");
        }

        $referenceNumber = 'WD-'.Str::upper(Str::random(10));
        $sourceAccount = config('paymongo.source_account');

        $payload = [
            'provider' => 'instapay',
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

        $result = $this->paymongo->createBatchTransfer($payload);
        $transferData = $result['body']['data']['transfers'][0] ?? null;

        $baseAttributes = [
            'wallet_id' => $wallet->id,
            'source_account_number' => $sourceAccount['number'] ?? null,
            'destination_account_number' => $data['account_number'],
            'destination_account_name' => $data['account_name'],
            'destination_account_bic' => $bic,
            'channel' => $channelId,
            'amount' => $amount,
            'fee' => self::TRANSFER_FEE,
            'provider' => 'instapay',
            'purpose' => $data['purpose'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'reference_number' => $referenceNumber,
            'raw_response' => $result['body'] ?? [],
        ];

        // Handle API Failure
        if (! $result['ok'] || ! $transferData) {
            $errorMessage = $result['body']['errors'][0]['detail'] ?? 'Transfer could not be processed. Please try again.';
            $failureCode = $result['body']['errors'][0]['code'] ?? 'api_error';

            BatchTransfer::create($baseAttributes + [
                'status' => 'failed',
                'failure_code' => $failureCode,
            ]);

            throw new DomainException($errorMessage);
        }

        // Handle Success inside Transaction
        return DB::transaction(function () use ($wallet, $totalDeduct, $baseAttributes, $result, $transferData, $channel, $data) {
            // Actually lock the row for update — wallet->fresh() does NOT lock.
            $lockedWallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            if (! $lockedWallet || (float) $lockedWallet->balance < $totalDeduct) {
                $batchTransfer = BatchTransfer::create($baseAttributes + [
                    'paymongo_batch_id' => $result['body']['data']['id'] ?? null,
                    'paymongo_transfer_id' => $transferData['id'] ?? null,
                    'status' => $transferData['status'] ?? 'pending',
                    'failure_code' => 'wallet_balance_mismatch',
                ]);

                Log::critical('PayMongo transfer succeeded but wallet balance could not be reconciled.', [
                    'batch_transfer_id' => $batchTransfer->id,
                    'wallet_id' => $wallet->id,
                    'total_deduct' => $totalDeduct,
                ]);

                return $batchTransfer->fresh();
            }

            $batchTransfer = BatchTransfer::create($baseAttributes + [
                'paymongo_batch_id' => $result['body']['data']['id'] ?? null,
                'paymongo_transfer_id' => $transferData['id'] ?? null,
                'status' => $transferData['status'] ?? 'pending',
            ]);

            $lockedWallet->decrement('balance', $totalDeduct);

            $batchTransfer->walletTransaction()->create([
                'wallet_id' => $lockedWallet->id,
                'amount' => $totalDeduct,
                'type' => 'debit',
                'description' => "Transfer to {$channel['name']} ({$data['account_number']})",
            ]);

            return $batchTransfer->fresh();
        });
    }
}
