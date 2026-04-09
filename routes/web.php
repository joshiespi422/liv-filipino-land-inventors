<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\UserType;

Route::inertia('/', 'Home', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware([
    'auth', 
    'role:' . UserType::SUPER_ADMIN . ',' . UserType::ADMIN
])->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
