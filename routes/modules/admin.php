<?php

use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BankSampah\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/persetujuan', [AdminController::class, 'approvals'])->name('approvals');
        Route::post('/persetujuan/{user}', [AdminController::class, 'approve'])->name('approvals.store');
        Route::post('/warga/{resident}/status', [AdminController::class, 'updateResidentStatus'])->name('residents.status');
        Route::get('/peran', [AdminController::class, 'roles'])->name('roles');
        Route::post('/peran', [AdminController::class, 'assignRole'])->name('roles.store');
        Route::get('/tarif', [AdminController::class, 'tariff'])->name('tariff');
        Route::get('/publikasi', [AdminController::class, 'publication'])->name('publication');
        Route::get('/fraud', [AdminController::class, 'fraud'])->name('fraud');
        Route::get('/kas', [AdminController::class, 'cash'])->name('cash');
        Route::get('/impor', [AdminController::class, 'import'])->name('import');
        Route::get('/kesiapan', [AdminController::class, 'readiness'])->name('readiness');
        Route::get('/audit', [AdminController::class, 'audit'])->name('audit');
        Route::get('/cadangan', [AdminController::class, 'backup'])->name('backup');

        Route::resource('materials', MaterialController::class)->except(['show', 'destroy']);
        Route::patch('materials/{material}/toggle-status', [MaterialController::class, 'toggleStatus'])->name('materials.toggle-status');
        Route::resource('rewards', RewardController::class)->except(['show', 'destroy']);
    });
