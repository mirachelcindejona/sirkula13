<?php

use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:pengelola-kegiatan,superadmin'])
    ->prefix('program')
    ->name('program.')
    ->group(function () {
        Route::get('/', [ProgramController::class, 'dashboard'])->name('dashboard');
        Route::get('/kegiatan', [ProgramController::class, 'events'])->name('events');
        Route::get('/katalog', [ProgramController::class, 'catalog'])->name('catalog');
        Route::get('/pesanan', [ProgramController::class, 'orders'])->name('orders');
    });
