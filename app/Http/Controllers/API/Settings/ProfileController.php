<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ChangePasswordRequest;
use App\Http\Requests\Api\User\UpdateProfileRequest;
use App\Http\Resources\Api\User\ProfileResource;
use App\Services\User\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {
    }

    /**
     * Get profile.
     *
     * Returns the authenticated user's profile. Eager-loads status
     * and userType relationships to pre-fill all form fields.
     *
     * @tags Settings > Profile
     *
     * @response scenario="success" {
     *   "data": {
     *     "id": 1,
     *     "name": "Juan dela Cruz",
     *     "phone": "09171234567",
     *     "email": "juan@example.com",
     *     "gender": "male",
     *     "birthdate": "1990-01-15",
     *     "region": "NCR",
     *     "province": "Metro Manila",
     *     "city": "Quezon City",
     *     "barangay": "Bagong Silangan",
     *     "street": "123 Rizal St.",
     *     "postal_code": "1110",
     *     "avatar": "https://example.com/storage/avatars/abc.jpg",
     *     "valid_id_type": "passport",
     *     "valid_id_number": "P1234567A",
     *     "front_valid_id_picture": "https://example.com/storage/valid-ids/front.jpg",
     *     "back_valid_id_picture": "https://example.com/storage/valid-ids/back.jpg",
     *     "is_verified": true,
     *     "status": { "id": 1, "name": "Pending" },
     *     "user_type": { "id": 2, "name": "Agent" }
     *   }
     * }
     */
    public function show(Request $request): ProfileResource
    {
        $user = $request->user()->load([
            'status',
            'userType',
        ]);

        return new ProfileResource($user);
    }

    /**
     * Update profile.
     *
     * Submits all profile data. Must use multipart/form-data due to file uploads.
     * On success the user enters a 2–3 day approval window.
     *
     * @tags Settings > Profile
     *
     * @bodyParam first_name string required User's first name. Example: Juan
     * @bodyParam last_name string required User's last name. Example: dela Cruz
     * @bodyParam phone string optional Contact phone number. Example: +639171234567
     * @bodyParam address string optional Residential address. Example: 123 Rizal St, Manila
     * @bodyParam bio string optional Short bio or description. Example: Licensed real estate broker.
     * @bodyParam id_document file optional Government-issued ID image (jpg/png/pdf). Max: 2MB.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Your account details have been completed. Please wait 2-3 days for approval. Updates will be sent to your email.",
     *   "data": {}
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "first_name": ["The first name field is required."]
     *   }
     * }
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->profileService->updateProfile(
            $request->user(),
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Your account details have been completed. Please wait 2-3 days for approval. Updates will be sent to your email.',
            'data' => new ProfileResource($updatedUser),
        ]);
    }

    /**
     * Change password.
     *
     * Verifies the user's current password then replaces it with a new one.
     * The new password must be confirmed and meet minimum security requirements (min 8 chars).
     *
     * @tags Settings > Profile
     *
     * @bodyParam current_password string required The user's existing password. Example: OldPass123
     * @bodyParam new_password string required New password, min 8 characters. Example: NewPass456
     * @bodyParam new_password_confirmation string required Must match new_password. Example: NewPass456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Password changed successfully."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Current password is incorrect."
     * }
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $success = $this->profileService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password,
        );

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Upload avatar.
     *
     * Accepts a single image file and replaces the user's current avatar.
     * Returns the new public storage URL.
     *
     * @tags Settings > Profile
     *
     * @bodyParam avatar file required Image file — jpg, png, gif. Max: 2MB.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Avatar updated.",
     *   "data": {
     *     "avatar": "https://example.com/storage/avatars/xyz.jpg"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "avatar": ["The avatar must be an image.", "The avatar must not be greater than 2048 kilobytes."]
     *   }
     * }
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $request->user()->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar updated.',
            'data' => [
                'avatar' => asset('storage/' . $path),
            ],
        ]);
    }
}
