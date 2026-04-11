<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\UserType;
use App\Http\Controllers\Web\BusinessTrainingController;
use App\Http\Controllers\Web\LoanAssistanceController;

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
        Route::get('/business-training', [BusinessTrainingController::class, 'index'])
            ->name('business-training.index');
        Route::get('/business-training/type/{slug}', [BusinessTrainingController::class, 'showType'])
            ->name('business-training.type.show');
        Route::get('/business-training/categories/{slug}/modules', [BusinessTrainingController::class, 'getCategoryModules'])
            ->name('business-training.modules');

        Route::get('/loan-assistance', [LoanAssistanceController::class, 'index'])
            ->name('loan-assistance.index');
    });

});

require __DIR__.'/settings.php';
