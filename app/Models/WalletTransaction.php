<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'description',
        'reference_id',
        'reference_type',
    ];

    // relationship to reference
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // relationship to wallet
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
