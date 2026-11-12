
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketplace\ProductController;

require __DIR__.'/modules/bank-sampah.php';

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index'])
    ->name('products.index');
