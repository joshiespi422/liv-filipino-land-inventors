<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'loan_schedule_id',
        'payment_method_id',
        'payment_date',
        'amount',

        'gateway',
        'gateway_payment_intent_id',
        'gateway_payment_id',
        'gateway_response',

        'status_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'gateway_response' => 'array',
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

    // one to many, payment has many wallet transactions
    public function walletTransactions(): MorphMany
    {
        return $this->morphMany(WalletTransaction::class, 'reference');
    }

    /**
     * Summary of status
     *
     * @return BelongsTo<Status, LoanPayment>
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
