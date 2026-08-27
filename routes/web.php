<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PersetujuanController;
use App\Models\Pemesanan;
use App\Models\Persetujuan;
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

    Route::middleware('can:viewAny,'.Pemesanan::class)->group(function () {
        Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::get('/pemesanan/create', [PemesananController::class, 'create'])->name('pemesanan.create');
        Route::post('/pemesanan', [PemesananController::class, 'store'])->name('pemesanan.store');
        Route::get('/pemesanan/ketersediaan', [PemesananController::class, 'ketersediaan'])->name('pemesanan.ketersediaan');
    });

    Route::middleware('can:viewAny,'.Persetujuan::class)->group(function () {
        Route::get('/persetujuan', [PersetujuanController::class, 'index'])->name('persetujuan.index');
        Route::get('/persetujuan/history', [PersetujuanController::class, 'history'])->name('persetujuan.history');
        Route::post('/persetujuan/{persetujuan}/approve', [PersetujuanController::class, 'approve'])
            ->name('persetujuan.approve');
        Route::post('/persetujuan/{persetujuan}/reject', [PersetujuanController::class, 'reject'])
            ->name('persetujuan.reject');
    });

    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});
