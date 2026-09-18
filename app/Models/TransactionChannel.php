<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionChannel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'search',
        'provider',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
