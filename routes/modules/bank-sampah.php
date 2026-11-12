<?php

use App\Http\Controllers\BankOperationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:operator-bank-sampah,superadmin'])
    ->prefix('bank-sampah')
    ->name('bank.')
    ->group(function () {
        Route::get('/', [BankOperationsController::class, 'dashboard'])->name('dashboard');
        Route::get('/setoran/baru', [BankOperationsController::class, 'createDeposit'])->name('deposits.create');
        Route::post('/setoran', [BankOperationsController::class, 'storeDeposit'])->name('deposits.store');
        Route::get('/verifikasi', [BankOperationsController::class, 'verify'])->name('verify');
        Route::post('/verifikasi/{deposit}', [BankOperationsController::class, 'verifyDeposit'])->name('verify.store');
        Route::get('/stok', [BankOperationsController::class, 'inventory'])->name('inventory');
        Route::get('/warga', [BankOperationsController::class, 'residents'])->name('residents');
        Route::post('/warga', [BankOperationsController::class, 'storeResident'])->name('residents.store');
        Route::get('/serah-terima', [BankOperationsController::class, 'handover'])->name('handover');
        Route::post('/serah-terima/{redemption}', [BankOperationsController::class, 'completeHandover'])->name('handover.store');
        Route::get('/impor', [BankOperationsController::class, 'import'])->name('import');
    });
