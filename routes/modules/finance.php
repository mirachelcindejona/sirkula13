<?php

use App\Http\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:bendahara,superadmin'])
    ->prefix('keuangan')
    ->name('finance.')
    ->group(function () {
        Route::get('/', [FinanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/pencairan', [FinanceController::class, 'payouts'])->name('payouts');
        Route::post('/pencairan/{payout}', [FinanceController::class, 'pay'])->name('payouts.pay');
        Route::get('/kas', [FinanceController::class, 'cashbook'])->name('cashbook');
        Route::get('/donasi', [FinanceController::class, 'donations'])->name('donations');
        Route::post('/donasi/{donation}', [FinanceController::class, 'verifyDonation'])->name('donations.verify');
        Route::get('/laporan', [FinanceController::class, 'reports'])->name('reports');
    });
