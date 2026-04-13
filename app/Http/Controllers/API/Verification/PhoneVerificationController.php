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
     * Verify OTP — Step 2.
     *
     * Verify the OTP sent to the phone number and receive a
     * verification token to be used in the set-password step.
     *
     * @tags Auth
     * @unauthenticated
     *
     * @response 200 {
     *   "message": "Phone verified. Please set your password.",
     *   "verification_token": "eyJhbGciOiJIUzI1NiJ9..."
     * }
     * @response 403 { "message": "Invalid or expired OTP." }
     * @response 404 { "message": "No pending registration found for this phone." }
     * @response 500 { "message": "Verification failed." }
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
     * Resend OTP.
     *
     * Resend an OTP to the given phone number. Only valid for
     * phone numbers with a pending registration.
     *
     * @tags Auth
     * @unauthenticated
     *
     * @response 200 scenario="OTP resent" {
     *   "status": "pending",
     *   "message": "OTP resent successfully.",
     *   "phone": "+639171234567"
     * }
     * @response 201 scenario="New OTP issued" {
     *   "status": "created",
     *   "message": "OTP sent.",
     *   "phone": "+639171234567"
     * }
     * @response 404 { "message": "No pending registration found for this phone." }
     * @response 429 { "message": "Too Many Attempts." }
     * @response 500 { "message": "Failed to resend OTP." }
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
