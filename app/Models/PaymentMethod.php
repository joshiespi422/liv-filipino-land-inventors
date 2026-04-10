<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    public $timestamps = false;

    public const CASH = 1;
    public const CREDIT_CARD = 2;
    public const QR_CODE = 3;
    
    // protected $fillable = [];

    // one to many, payment method has many loan payments
    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
