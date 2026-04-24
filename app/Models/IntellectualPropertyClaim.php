<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntellectualPropertyClaim extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'intellectual_property_id',
        'description'
    ];

    public function intellectualProperty(): BelongsTo
    {
        return $this->belongsTo(IntellectualProperty::class);
    }
}
