<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsAndConditionCCNPH extends Model
{
    protected $table = 'terms_and_conditions_ccnph';

    protected $fillable = [
        'name',
        'content',
    ];
}
