<?php

namespace App\Services\User;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function updateProfile(User $user, UpdateProfileRequest $request): User
    {
        $data = $request->validated();

        $data['front_valid_id_picture'] = $this->storeIdPhoto(
            $request->file('front_valid_id_picture')
        );

        $data['back_valid_id_picture'] = $this->storeIdPhoto(
            $request->file('back_valid_id_picture')
        );

        $user->update($data);

        return $user->fresh();
    }

    private function storeIdPhoto(UploadedFile $file): string
    {
        return $file->store('valid_ids', 'public');
    }

    // Password change logic
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // revoke tokens
        // $user->tokens()->delete();

        return true;
    }
}
