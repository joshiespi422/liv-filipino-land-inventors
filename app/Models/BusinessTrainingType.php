<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessTrainingType extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    /**
     * Use slug for Route Model Binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relationship: One Type has many Categories
     */
    public function categories(): HasMany
    {
        return $this->hasMany(BusinessTrainingCategory::class);
    }
}
