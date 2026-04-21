<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntellectualPropertyDocument extends Model
{
    protected $fillable = [
        'intellectual_property_id',
        'attachment',
    ];

    public function intellectualProperty(): BelongsTo
    {
        return $this->belongsTo(IntellectualProperty::class);
    }
}
