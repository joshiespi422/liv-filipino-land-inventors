<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanSchedule extends Model
{
    protected $fillable = [
        'loan_id',
        'status_id',
        'month_no',
        'beginning_balance',
        'interest_amount',
        'principal_amount',
        'total_payment',
        'ending_balance',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // one to many, schedule has one loan
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    // one to many, schedule has one status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // one to many, schedule has many payments
    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
