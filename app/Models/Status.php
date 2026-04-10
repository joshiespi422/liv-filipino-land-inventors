<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    public $timestamps = false;

    public const PENDING = 1;
    public const APPROVED = 2;
    public const REJECTED = 3;
    public const CANCELLED = 4;
    public const ACTIVE = 5;
    public const FINISHED = 6;
    public const PAID = 7;
    public const UNPAID = 8;
    public const OVERDUE = 9;
    
    // protected $fillable = [];

}
