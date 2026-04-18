<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            LoanSchedule::class,
            'loan_id',
            'payable_id',
        )->where('payments.payable_type', LoanSchedule::class);
    }

    // called by LoanSchedule::onPaymentSuccess after each paid schedule
    public function tryFinish(): void
    {
        $hasUnpaid = $this->loanSchedules()
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if (!$hasUnpaid) {
            $this->update(['status_id' => Status::FINISHED]);
        }
    }
}
