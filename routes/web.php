<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => Inertia\Inertia::render('Auth/Login'))->name('login');
    Route::get('/register', fn () => Inertia\Inertia::render('Auth/Register'))->name('register');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.submit');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.submit');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});
