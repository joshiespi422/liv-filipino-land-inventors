<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipSetting extends Model
{
    protected $fillable = [
        'share_capital_amount',
        'allowed_term_months',
    ];

    protected $casts = [
        'share_capital_amount' => 'integer',
        'allowed_term_months' => 'array',
    ];

    public static function current(): self
    {
        return static::latest()->firstOrFail();
    }
}
