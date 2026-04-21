<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntellectualProperty extends Model
{
    protected $fillable = [
        'user_id',
        'status_id',
        'payment_id',
        'creation_type',
        'form_type',
        'title',
        'description',
        'applicability',
    ];

    // one to many, intellectual property has one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // one to many, intellectual property has one status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // one to many, intellectual property has one payment
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // one to many, intellectual property has many claims
    public function claims(): HasMany
    {
        return $this->hasMany(IntellectualPropertyClaim::class);
    }

    // one to many, intellectual property has many documents
    public function documents(): HasMany
    {
        return $this->hasMany(IntellectualPropertyDocument::class);
    }
}
