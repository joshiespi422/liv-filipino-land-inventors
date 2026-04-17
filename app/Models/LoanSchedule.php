<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'status_id' => 'integer',
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
    // public function loanPayments(): HasMany
    // {
    //     return $this->hasMany(LoanPayment::class);
    // }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        $schedule = LoanSchedule::findOrFail($payment->meta['loan_schedule_id']);
        $schedule->update(['status_id' => Status::PAID]);
        $this->tryFinish();
    }

    public function onPaymentFailed(Payment $payment): void
    {
        // notify, log, etc.
    }

    private function tryFinish(): void
    {
        $hasUnpaid = $this->loanSchedules()
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if (!$hasUnpaid) {
            $this->update(['status_id' => Status::FINISHED]);
        }
    }
}
