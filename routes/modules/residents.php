<?php

use App\Http\Controllers\CitizenController;
use App\Http\Controllers\RewardRedemptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:warga'])->group(function () {
    Route::get('/profil', [CitizenController::class, 'profile'])->name('citizen.profile');
    Route::get('/setoran', [CitizenController::class, 'deposits'])->name('citizen.deposits');
    Route::get('/poin', [CitizenController::class, 'points'])->name('citizen.points');
    Route::post('/poin/tukar', [RewardRedemptionController::class, 'redeem'])->name('citizen.rewards.redeem');
    Route::get('/transaksi-saya', [CitizenController::class, 'orders'])->name('citizen.orders');
    Route::post('/transaksi-saya/{order}/batal', [CitizenController::class, 'cancelPurchase'])->name('citizen.orders.cancel');
    Route::get('/produk-saya', [CitizenController::class, 'products'])->name('citizen.products');
    Route::get('/produk-saya/baru', [CitizenController::class, 'createProduct'])->name('citizen.products.create');
    Route::post('/produk-saya', [CitizenController::class, 'storeProduct'])->name('citizen.products.store');
    Route::get('/produk-saya/{product}/ubah', [CitizenController::class, 'editProduct'])->name('citizen.products.edit');
    Route::put('/produk-saya/{product}', [CitizenController::class, 'updateProduct'])->name('citizen.products.update');
    Route::post('/produk-saya/pesanan/{order}/selesai', [CitizenController::class, 'completeSale'])->name('citizen.sales.complete');
    Route::post('/produk-saya/pesanan/{order}/batal', [CitizenController::class, 'cancelSale'])->name('citizen.sales.cancel');
});
