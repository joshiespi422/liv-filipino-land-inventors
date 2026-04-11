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
     * Step 1: Authenticate via phone + password, return token.
     */
    public function store(LoginRequest $request, AuthenticationService $authService): JsonResponse
    {
        try {
            $data = $authService->login($request);

            return response()->json([
                'message' => 'Login successful.',
                'token' => $data['token'],
                'user' => $data['user'],
            ], 200);

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
     * Revoke the current token and log out.
     */
    public function destroy(Request $request, AuthenticationService $authService): JsonResponse
    {
        if ($request->user()) {
            $authService->logout($request->user());
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }
}
