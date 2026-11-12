<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Modul A2 - Manajemen Warga
require __DIR__.'/modules/residents.php';

// Modul A2 - Kesiapan Operasional RW
require __DIR__.'/modules/readiness.php';