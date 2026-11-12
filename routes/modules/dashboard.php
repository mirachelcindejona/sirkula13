<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/beranda', [DashboardController::class, 'home'])->name('app.home');
});
