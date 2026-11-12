<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthController::class, 'store'])->name('login.store');
    Route::get('/daftar', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
    Route::view('/lupa-kata-sandi', 'pages.auth.forgot')->name('password.request');
    Route::post('/lupa-kata-sandi', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/keluar', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
