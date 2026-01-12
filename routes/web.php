<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnsubscribeEmailController;
use App\Http\Controllers\UnlistedEmailController;
use App\Http\Controllers\Auth\GoogleAuthController;

Route::get('/', function () {
    return view('welcome');
});

// Google OAuth Routes
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

Route::prefix('dashboard')->middleware(['auth', 'auto.sync.gmail'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    
    // Unlisted Emails Management Routes
    Route::get('/unlisted-emails', [UnlistedEmailController::class, 'index'])->name('unlisted.index');
    
    // Unsubscribe Management Routes
    Route::get('/unsubscribe-emails', [UnsubscribeEmailController::class, 'index'])->name('unsubscribe.index');
});