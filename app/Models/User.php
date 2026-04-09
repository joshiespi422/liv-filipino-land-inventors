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

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
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

    // checker for service management
    public function managesService($serviceId): bool
    {
        // Super Admins bypass 
        if ($this->user_type_id === UserType::SUPER_ADMIN && $this->is_active) {
            return true;
        }

        // Must be an Admin and currently Active
        if ($this->user_type_id !== UserType::ADMIN || !$this->is_active) {
            return false;
        }

        // Must be assigned to the service AND the service must be active
        return $this->services()
            ->where('services.id', $serviceId)
            ->where('services.is_active', true)
            ->exists();
    }
}
