<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'loan_schedule_id',
        'payment_method_id',
        'payment_date',
        'amount',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    // one to many, payment has one loan
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    // one to many, payment has one schedule
    public function loanSchedule(): BelongsTo
    {
        return $this->belongsTo(LoanSchedule::class);
    }

    // one to many, payment has one payment method
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

}
