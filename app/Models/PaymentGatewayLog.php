<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayLog extends Model
{
    protected $fillable = [
        'loan_payment_id',
        'gateway',
        'event',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function loanPayment()
    {
        return $this->belongsTo(LoanPayment::class);
    }
}
