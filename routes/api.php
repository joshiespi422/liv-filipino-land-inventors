<?php

use App\Http\Controllers\API\Auth\AuthenticatedSessionController;
use App\Http\Controllers\API\Auth\RegisteredUserController;
use App\Http\Controllers\API\BusinessTraining\CategoryController;
use App\Http\Controllers\API\BusinessTraining\TrainingController;
use App\Http\Controllers\API\BusinessTraining\TypeController;
use App\Http\Controllers\API\Settings\ProfileController;
use App\Http\Controllers\API\Verification\PhoneVerificationController;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    // Auth
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1');

    // Verification
    Route::post('/verify-phone/acknowledge', [PhoneVerificationController::class, 'verify'])->middleware('throttle:5,1');
    Route::post('/verify-phone/resend', [PhoneVerificationController::class, 'resend'])->middleware('throttle:3,1');
    Route::post('/register/set-password', [RegisteredUserController::class, 'setPassword'])->middleware('throttle:5,1');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    // Business Training Routes
    Route::prefix('business-training')
        ->middleware('role.api:' . UserType::BASIC)
        ->group(function () {
            // Types
            Route::get('types', [TypeController::class, 'index']);
            Route::get('types/{type}', [TypeController::class, 'show']);

            // Categories under a Type
            Route::get('types/{type}/categories', [CategoryController::class, 'index']);
            Route::get('types/{type}/categories/{category}', [CategoryController::class, 'show']);

            // Training Modules under a Category
            Route::get('categories/{category}/trainings', [TrainingController::class, 'index']);
            Route::get('categories/{category}/trainings/{module}', [TrainingController::class, 'show'])
                ->whereNumber('module');
        });

    // Profile Routes
    Route::prefix('profile')
        ->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::post('update', [ProfileController::class, 'update']);
            Route::patch('change-password', [ProfileController::class, 'changePassword']);
            Route::post('avatar', [ProfileController::class, 'updateAvatar']);
        });
});
