<?php

use App\Http\Controllers\API\Auth\AuthenticatedSessionController;
use App\Http\Controllers\API\Auth\RegisteredUserController;
use App\Http\Controllers\API\Verification\PhoneVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    // Auth
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1');

    // Verification
    Route::post('/verify-phone/acknowledge', [PhoneVerificationController::class, 'verify'])->middleware('throttle:5,1');
    Route::post('/verify-phone/resend', [PhoneVerificationController::class, 'resend'])->middleware('throttle:3,1');
    Route::post('/register/set-password', [RegisteredUserController::class, 'setPassword'])->middleware('throttle:5,1');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
