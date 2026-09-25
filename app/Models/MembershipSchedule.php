<?php

namespace App\Models;

use App\Contracts\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MembershipSchedule extends Model implements Payable
{
    protected $fillable = [
        'member_membership_id',
        'status_id',
        'installment_no',
        'amount',
        'due_date',
    ];

    protected $casts = [
        'status_id' => 'integer',
        'amount' => 'integer',
        'due_date' => 'date',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(MemberMembership::class, 'member_membership_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        $this->update(['status_id' => Status::PAID]);

        $this->recordLedgerEntry($payment);

        $this->membership->tryActivate();
    }

    public function onPaymentFailed(Payment $payment): void
    {
        //
    }

    /**
     * Write this membership installment payment into the shared
     * wallet_transactions ledger (no actual Wallet is involved —
     * wallet_id stays null for membership-originated rows).
     */
    private function recordLedgerEntry(Payment $payment): void
    {
        $amountPesos = $payment->amount / 100;
        $feePesos = ($payment->fee ?? 0) / 100;

        $providerLabel = $payment->paymentMethod?->name ?? ucfirst($payment->gateway);

        WalletTransaction::create([
            'wallet_id' => null,
            'reference_type' => self::class,
            'reference_id' => $this->id,
            'reference_number' => $payment->gateway_payment_intent_id,
            'type' => 'membership_payment',
            'amount' => $amountPesos,
            'transfer_fee' => $feePesos,
            'from_name' => $this->membership->user?->name,
            'to_account_name' => 'FISMPC Membership',
            'to_account_number' => (string) $this->member_membership_id,
            'to_provider' => $providerLabel,
            'description' => 'Membership installment '.$this->installment_no.' payment via '.$providerLabel,
        ]);
    }

    public function cooperativeServiceSlug(): ?string
    {
        return 'coop-membership';
    }
}
