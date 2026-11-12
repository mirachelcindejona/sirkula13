<?php

use App\Http\Controllers\Readiness\ReadinessController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('rw/kesiapan')->name('readiness.')->group(function (): void {
    Route::get('/', [ReadinessController::class, 'index'])->name('index');
    Route::put('/', [ReadinessController::class, 'update'])->name('update');
});
