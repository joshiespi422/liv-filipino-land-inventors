<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthenticationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login.
     *
     * Authenticate via phone + password and receive a Bearer token.
     *
     * @tags Auth
     * @unauthenticated
     *
     * @response 200 {
     *   "message": "Login successful.",
     *   "token": "1|abc123...",
     *   "token_type": "Bearer",
     *   "user": {
     *     "id": 1,
     *     "name": "Juan dela Cruz",
     *     "phone": "+639171234567"
     *   }
     * }
     * @response 422 {
     *   "message": "Invalid credentials.",
     *   "errors": {
     *     "phone": ["These credentials do not match our records."]
     *   }
     * }
     * @response 403 {
     *   "message": "Your account has been suspended."
     * }
     * @response 500 {
     *   "message": "Something went wrong."
     * }
     */
    public function store(LoginRequest $request, AuthenticationService $authService): JsonResponse
    {
        try {
            $data = $authService->login($request);

            return response()->json([
                'message' => 'Login successful.',
                'token' => $data['token'],
                'token_type' => 'Bearer',
                'user' => $data['user'],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Exception) {
            return response()->json([
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    /**
     * Logout.
     *
     * Revoke the current Bearer token and end the session.
     *
     * @tags Auth
     *
     * @response 200 {
     *   "message": "Logged out successfully."
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */
    public function destroy(Request $request, AuthenticationService $authService): JsonResponse
    {
        if ($request->user()) {
            $authService->logout($request->user());
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
