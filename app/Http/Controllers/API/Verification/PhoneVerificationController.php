<?php

namespace App\Http\Controllers\API\Verification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Verification\ResendOtpRequest;
use App\Http\Requests\Verification\VerifyOtpRequest;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Throwable;

class PhoneVerificationController extends Controller
{
    public function __construct(private RegistrationService $registrationService)
    {
    }

    /**
     * Step 2: Verify OTP and return a verification token for the set-password step.
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $token = $this->registrationService->verifyPhone(
                $request->validated('phone'),
                $request->validated('otp_code'),
            );

            return response()->json([
                'message' => 'Phone verified. Please set your password.',
                'verification_token' => $token,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Verification failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend OTP to the given phone number.
     */
    public function resend(ResendOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->registrationService->resendOtp(
                $request->validated('phone'),
            );

            return response()->json($result, $result['status'] === 'pending' ? 200 : 201);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to resend OTP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
