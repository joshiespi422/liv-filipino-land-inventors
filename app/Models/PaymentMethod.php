<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    public $timestamps = false;

    public const CASH = 1;
    public const CREDIT_CARD = 2;
    public const QR_CODE = 3;
    
    // protected $fillable = [];
}
