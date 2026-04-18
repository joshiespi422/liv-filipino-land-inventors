<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

class RegistrationController extends Controller
{
    public function __construct(private RegistrationService $registrationService)
    {
    }

    /**
     * Web Step 1: Initiate OTP
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'min:10', 'max:15', 'unique:users,phone'],
        ], [
            'mobile_number.unique' => 'This mobile number is already registered to an account.'
        ]);

        try {
            $phone = $this->formatPhone($validated['mobile_number']);
            $result = $this->registrationService->initiateRegistration($validated['full_name'], $phone);
            
            return response()->json($result, $result['status'] === 'pending' ? 200 : 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->getValidHttpCode((int) $e->getCode()));

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to initiate registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Web Step 2: Verify OTP
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile_number' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        try {
            $phone = $this->formatPhone($validated['mobile_number']);
            $token = $this->registrationService->verifyPhone($phone, $validated['otp']);

            return response()->json([
                'status' => 'verified',
                'verification_token' => $token
            ], 200);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->getValidHttpCode((int) $e->getCode()));

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to verify OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Web Step 3: Complete Registration
     * Returns JSON so the Vue fetch API can parse it and proceed to Step 4
     */
    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile_number' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
            'verification_token' => ['required', 'string'],
        ]);

        try {
            $phone = $this->formatPhone($validated['mobile_number']);
            
            $result = $this->registrationService->completeRegistration(
                $phone,
                $validated['password'],
                $validated['verification_token']
            );

            // Log the user in via session (Web only)
            Auth::login($result['user']);

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully.'
            ], 200);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->getValidHttpCode((int) $e->getCode()));

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to complete registration.', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Web: Resend OTP
     */
    public function resend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile_number' => ['required', 'string'],
        ]);

        try {
            $phone = $this->formatPhone($validated['mobile_number']);
            $result = $this->registrationService->resendOtp($phone);
            
            return response()->json($result, $result['status'] === 'pending' ? 200 : 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->getValidHttpCode((int) $e->getCode()));

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to resend OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Format PH mobile numbers for Movider (e.g. 0912... to 63912...)
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '09')) {
            return '63' . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Helper: Ensure exceptions return a valid HTTP status code
     */
    private function getValidHttpCode(int $code): int
    {
        // If the code is a valid HTTP status (100-599), return it. Otherwise, return 400 Bad Request.
        return ($code >= 100 && $code <= 599) ? $code : 400;
    }
}