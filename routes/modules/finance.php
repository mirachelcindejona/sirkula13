<?php

use App\Http\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:operator-bank-sampah,bendahara,superadmin'])
    ->prefix('keuangan')
    ->name('finance.')
    ->group(function () {
        Route::get('/pencairan', [FinanceController::class, 'payouts'])->name('payouts');
        Route::post('/pencairan/{payout}', [FinanceController::class, 'pay'])->name('payouts.pay');
        Route::post('/pencairan/{payout}/batal', [FinanceController::class, 'reversePayout'])->name('payouts.reverse');
    });

Route::middleware(['auth', 'role:bendahara,superadmin'])
    ->prefix('keuangan')
    ->name('finance.')
    ->group(function () {
        Route::get('/', [FinanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/kas', [FinanceController::class, 'cashbook'])->name('cashbook');
        Route::get('/kas/baru', [FinanceController::class, 'createCash'])->name('cashbook.create');
        Route::post('/kas', [FinanceController::class, 'storeCash'])->name('cashbook.store');
        Route::get('/kas/{cash}/ubah', [FinanceController::class, 'editCash'])->name('cashbook.edit');
        Route::put('/kas/{cash}', [FinanceController::class, 'updateCash'])->name('cashbook.update');
        Route::delete('/kas/{cash}', [FinanceController::class, 'destroyCash'])->name('cashbook.destroy');
        Route::get('/donasi', [FinanceController::class, 'donations'])->name('donations');
        Route::post('/donasi/{donation}', [FinanceController::class, 'verifyDonation'])->name('donations.verify');
        Route::get('/saluran-donasi', [FinanceController::class, 'channel'])->name('channel');
        Route::post('/saluran-donasi', [FinanceController::class, 'updateChannel'])->name('channel.update');
        Route::get('/laporan', [FinanceController::class, 'reports'])->name('reports');
        Route::get('/laporan/excel', [FinanceController::class, 'exportReports'])->name('reports.export');
    });
