<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoticeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/beranda', [DashboardController::class, 'home'])->name('app.home');
    Route::get('/notifikasi', [NoticeController::class, 'index'])->name('notices.index');
    Route::get('/notifikasi/baru', [NoticeController::class, 'poll'])->name('notices.poll');
    Route::get('/notifikasi/{notice}/baca', [NoticeController::class, 'read'])->name('notices.read');
    Route::post('/notifikasi/baca-semua', [NoticeController::class, 'readAll'])->name('notices.read-all');
});
