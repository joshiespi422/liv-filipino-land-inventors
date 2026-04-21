<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntellectualPropertySetting extends Model
{
    protected $fillable = [
        'amount',
        'allowed_term_months',
    ];

    protected $casts = [
        'amount' => 'integer',
        'allowed_term_months' => 'array',
    ];
}
