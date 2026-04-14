<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\UserType;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\BusinessTrainingController;
use App\Http\Controllers\Web\LoanAssistanceController;
use App\Http\Controllers\Web\LoanScheduleController;

Route::inertia('/', 'Home', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware([
    'auth', 
    'role:' . UserType::SUPER_ADMIN . ',' . UserType::ADMIN
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index');
    Route::get('/dashboard/users/{user}', [DashboardController::class, 'show'])
        ->name('dashboard.users.show');
    Route::patch('/dashboard/users/{user}/status', [DashboardController::class, 'updateStatus'])
        ->name('dashboard.users.update-status');

    // Business Training Domain
    Route::middleware(['service_access:business-training'])->group(function () {
        Route::get('/business-training', [BusinessTrainingController::class, 'index'])
            ->name('business-training.index');
        Route::get('/business-training/type/{slug}', [BusinessTrainingController::class, 'showType'])
            ->name('business-training.type.show');
        Route::get('/business-training/categories/{slug}/modules', [BusinessTrainingController::class, 'getCategoryModules'])
            ->name('business-training.modules');
    });

    // Loan Assistance Domain
    Route::middleware(['service_access:loan-assistance'])->group(function () {
        Route::get('/loan-assistance', [LoanAssistanceController::class, 'index'])
            ->name('loan-assistance.index');

        Route::get('/loan-assistance/{loan}/schedule', [LoanScheduleController::class, 'index'])
            ->name('loan-assistance.schedule.index');
    });

});

require __DIR__.'/settings.php';
