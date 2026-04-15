<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    // protected $fillable = [];

    // relationship to wallet transactions
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // relationship to user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
