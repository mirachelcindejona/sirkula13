<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('public.home');
Route::get('/profil-rw', [PublicController::class, 'profile'])->name('public.profile');
Route::get('/panduan', [PublicController::class, 'guide'])->name('public.guide');
Route::get('/kegiatan', [PublicController::class, 'events'])->name('public.events');
Route::post('/kegiatan/{event}/daftar', [PublicController::class, 'registerEvent'])->middleware('auth')->name('public.events.register');
Route::post('/kegiatan/{event}/batal', [PublicController::class, 'cancelEvent'])->middleware('auth')->name('public.events.cancel');
Route::get('/katalog', [PublicController::class, 'catalog'])->name('public.catalog');
Route::post('/katalog/{product}/pesan', [PublicController::class, 'orderProduct'])->middleware('auth')->name('public.catalog.order');
Route::get('/peringkat', [PublicController::class, 'leaderboard'])->name('public.leaderboard');
Route::get('/donasi', [PublicController::class, 'donation'])->name('public.donation');
Route::post('/donasi', [PublicController::class, 'storeDonation'])->name('public.donation.store');
