<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'user_type_id',
    'is_active',
    'phone',
    'phone_verified_at',
    'gender',
    'region',
    'province',
    'city',
    'barangay',
    'street',
    'postal_code',
])]

#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    use HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // relationship to user type, one to many
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    // relationship to services, many to many
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    // relationship to loans, one to many
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // relationship to loan settings, one to many
    public function loanSettings(): HasMany
    {
        return $this->hasMany(LoanSetting::class);
    }

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // checker for service management
    public function managesService($serviceId): bool
    {
        // Super Admins bypass
        if ($this->user_type_id === UserType::SUPER_ADMIN && $this->status_id === Status::ACTIVE) {
            return true;
        }

        // Must be an Admin and currently Active
        if ($this->user_type_id !== UserType::ADMIN || $this->status_id !== Status::ACTIVE) {
            return false;
        }

        // Must be assigned to the service AND the service must be active
        return $this->services()
            ->where('services.id', $serviceId)
            ->where('services.is_active', true)
            ->exists();
    }

    // instance method to get active loan setting
    public function getActiveLoanSetting()
    {
        return $this->loanSettings()
            ->orWhereNull('user_id')
            ->orderByRaw('user_id DESC')
            ->first();
    }
}
