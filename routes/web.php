<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\UserType;
use App\Http\Controllers\Web\BusinessTrainingController;

Route::inertia('/', 'Home', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware([
    'auth', 
    'role:' . UserType::SUPER_ADMIN . ',' . UserType::ADMIN
])->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

    // Business Training Domain
    Route::middleware(['service_access:business-training'])->group(function () {
        Route::get('/business-training', [BusinessTrainingController::class, 'index'])->name('business-training.index');
    });

});

require __DIR__.'/settings.php';
