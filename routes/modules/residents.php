<?php

use App\Http\Controllers\Residents\ResidentApprovalController;
use App\Http\Controllers\Residents\ResidentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('rw/warga')->name('residents.')->group(function (): void {
    Route::get('/', [ResidentController::class, 'index'])->name('index');
    Route::get('/manual/create', [ResidentController::class, 'create'])->name('create');
    Route::post('/manual', [ResidentController::class, 'store'])->name('store');
    Route::get('/pendaftaran', [ResidentApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/{resident}', [ResidentController::class, 'show'])->name('show');
    Route::get('/{resident}/edit', [ResidentController::class, 'edit'])->name('edit');
    Route::put('/{resident}', [ResidentController::class, 'update'])->name('update');
    Route::post('/{resident}/review', [ResidentApprovalController::class, 'review'])->name('approvals.review');
    Route::post('/{resident}/link', [ResidentApprovalController::class, 'link'])->name('approvals.link');
});
