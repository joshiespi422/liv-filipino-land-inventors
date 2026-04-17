<?php

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WalletService
{
    /**
     * Get or create the user's wallet.
     */
    public function getUserWallet(User $user): Wallet
    {
        // Ensures a wallet exists for the user (firstOrCreate pattern)
        return $user->wallet ?: $user->wallet()->create(['balance' => 0]);
    }

    /**
     * Get paginated transactions.
     */
    public function getWalletTransactions(Wallet $wallet): LengthAwarePaginator
    {
        return $wallet->walletTransactions()
            ->with('reference')
            ->latest()
            ->paginate(15);
    }
}
