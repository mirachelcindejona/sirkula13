<?php

use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:pengelola-kegiatan,superadmin'])
    ->prefix('program')
    ->name('program.')
    ->group(function () {
        Route::get('/', [ProgramController::class, 'dashboard'])->name('dashboard');
        Route::get('/kegiatan', [ProgramController::class, 'events'])->name('events');
        Route::get('/kegiatan/baru', [ProgramController::class, 'createEvent'])->name('events.create');
        Route::post('/kegiatan', [ProgramController::class, 'storeEvent'])->name('events.store');
        Route::get('/kegiatan/{event}', [ProgramController::class, 'showEvent'])->name('events.show');
        Route::put('/kegiatan/{event}', [ProgramController::class, 'updateEvent'])->name('events.update');
        Route::post('/kegiatan/{event}/kehadiran', [ProgramController::class, 'storeAttendance'])->name('events.attendance');
        Route::get('/katalog', [ProgramController::class, 'catalog'])->name('catalog');
        Route::get('/katalog/baru', [ProgramController::class, 'createProduct'])->name('catalog.create');
        Route::post('/katalog', [ProgramController::class, 'storeProduct'])->name('catalog.store');
        Route::get('/katalog/{product}/ubah', [ProgramController::class, 'editProduct'])->name('catalog.edit');
        Route::put('/katalog/{product}', [ProgramController::class, 'updateProduct'])->name('catalog.update');
        Route::get('/pesanan', [ProgramController::class, 'orders'])->name('orders');
        Route::post('/pesanan/{order}/selesai', [ProgramController::class, 'completeOrder'])->name('orders.complete');
        Route::post('/pesanan/{order}/tahan', [ProgramController::class, 'holdOrder'])->name('orders.hold');
        Route::post('/pesanan/{order}/lepas', [ProgramController::class, 'releaseOrder'])->name('orders.release');
    });
