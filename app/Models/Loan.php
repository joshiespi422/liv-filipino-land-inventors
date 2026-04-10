<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'status_id',
        'amount',
        'interest_rate',
        'term_months',
        'start_date',
        'end_date',
        'monthly_principal',
    ];

    // one to many, loan has one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // one to many, loan has one status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // one to many, loan has many schedules
    public function loanSchedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class);
    }

    // one to many, loan has many payments
    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
