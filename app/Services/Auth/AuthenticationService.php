<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;

class AuthenticationService
{
    /**
     * Authenticate a user via phone + password and issue a token.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(LoginRequest $request): array
    {
        $request->authenticate();

        $user = $request->user();

        // Prevent login if phone is not verified
        if (!$user->phone_verified_at) {
            throw new \RuntimeException('Phone number is not verified.', 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Revoke the user's current access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
