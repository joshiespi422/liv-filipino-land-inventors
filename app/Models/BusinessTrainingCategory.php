<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessTrainingCategory extends Model
{
    protected $fillable = [
        'business_training_type_id',
        'name',
        'slug',
        'description'
    ];

    /**
     * Use slug for Route Model Binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Inverse Relationship: Category belongs to a Type
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(BusinessTrainingType::class);
    }

    /**
     * Relationship: Category has many Training Modules
     */
  public function trainings(): HasMany
{
    return $this->hasMany(BusinessTraining::class);
}
}
