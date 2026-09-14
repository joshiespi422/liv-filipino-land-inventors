<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Authenticate a user via phone + password and issue a token.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): array
    {
        $this->restoreIfWithinGracePeriod(
            $request->validated('phone'),
            $request->validated('password'),
        );

        $request->authenticate();

        $user = $request->user();

        // Prevent login if phone is not verified
        if (! $user->phone_verified_at) {
            throw new \RuntimeException('Phone number is not verified.', 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Restore a soft-deleted account if it's within its 30-day
     * grace period and the credentials match.
     */
    private function restoreIfWithinGracePeriod(string $phone, string $password): void
    {
        $normalizedPhone = str_starts_with($phone, '63') ? '0'.substr($phone, 2) : $phone;

        $user = User::onlyTrashed()->where('phone', $normalizedPhone)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return;
        }

        if ($user->scheduled_deletion_at && $user->scheduled_deletion_at->isFuture()) {
            $user->restore();
            $user->update([
                'deletion_requested_at' => null,
                'scheduled_deletion_at' => null,
                'deletion_verification_request_id' => null,
                'deletion_otp_sent_at' => null,
                'deletion_token' => null,
            ]);
        }
    }

    /**
     * Revoke the user's current access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
