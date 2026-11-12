<?php

use App\Http\Controllers\BankSampah\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('bank-sampah')
    ->name('bank-sampah.')
    ->group(function () {

        Route::resource('materials', MaterialController::class)
            ->except(['show', 'destroy']);

        Route::patch(
            'materials/{material}/toggle-status',
            [MaterialController::class, 'toggleStatus']
        )->name('materials.toggle-status');

    });
