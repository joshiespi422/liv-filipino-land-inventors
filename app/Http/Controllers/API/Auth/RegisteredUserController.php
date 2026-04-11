<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Throwable;

class RegisteredUserController extends Controller
{
    public function __construct(private RegistrationService $registrationService)
    {
    }

    /**
     * Step 1: Accept name + phone, send OTP via Movider.
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        try {
            $this->registrationService->initiateRegistration(
                $request->validated('name'),
                $request->validated('phone'),
            );

            return response()->json([
                'message' => 'OTP sent to your phone number.',
            ], 201);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Step 3: Set password and create the real User record.
     */
    public function setPassword(SetPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->registrationService->completeRegistration(
                $request->validated('phone'),
                $request->validated('password'),
                $request->validated('verification_token'),
            );

            return response()->json([
                'message' => 'Registration complete.',
                'access_token' => $result['token'],
                'token_type' => 'Bearer',
                'user' => $result['user'],
            ], 201);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to complete registration.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
