<?php

use App\Http\Controllers\CitizenController;
use App\Http\Controllers\RewardRedemptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:warga'])->group(function () {
    Route::get('/profil', [CitizenController::class, 'profile'])->name('citizen.profile');
    Route::get('/setoran', [CitizenController::class, 'deposits'])->name('citizen.deposits');
    Route::get('/poin', [CitizenController::class, 'points'])->name('citizen.points');
    Route::post('/poin/tukar', [RewardRedemptionController::class, 'redeem'])->name('citizen.rewards.redeem');
    Route::get('/transaksi-saya', [CitizenController::class, 'orders'])->name('citizen.orders');
});
