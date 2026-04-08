<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    public $timestamps = false;

    public const SUPER_ADMIN = 1;
    public const ADMIN = 2;
    public const MEMBER = 3;
    public const BASIC = 4;

    // protected $fillable = [];

    // relationship to users, one to many
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

}
