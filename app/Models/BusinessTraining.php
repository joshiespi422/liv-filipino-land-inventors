<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTraining extends Model
{
    protected $fillable = [
        'business_training_categories_id', 
        'module', 
        'content'
    ];

    /**
     * Essential: Cast the JSON content column to an array
     */
    protected $casts = [
        'content' => 'array',
    ];

    /**
     * Inverse Relationship: Training belongs to a Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessTrainingCategory::class);
    }
}
