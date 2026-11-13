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
        Route::get('/setoran', [BankOperationsController::class, 'records'])->name('records');
        Route::get('/stok', [BankOperationsController::class, 'inventory'])->name('inventory');
        Route::post('/stok', [BankOperationsController::class, 'storeOutflow'])->name('inventory.store');
        Route::get('/warga', [BankOperationsController::class, 'residents'])->name('residents');
        Route::post('/warga', [BankOperationsController::class, 'storeResident'])->name('residents.store');
        Route::get('/serah-terima', [BankOperationsController::class, 'handover'])->name('handover');
        Route::post('/serah-terima/{redemption}', [BankOperationsController::class, 'completeHandover'])->name('handover.store');
        Route::post('/serah-terima/{redemption}/batal', [BankOperationsController::class, 'cancelHandover'])->name('handover.cancel');
    });
