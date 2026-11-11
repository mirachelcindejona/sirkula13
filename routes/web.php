
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketplace\ProductController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\RewardRedemptionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index'])
    ->name('products.index');

Route::post('/users/{id}/add-points', [PointController::class, 'addPoints']);
Route::get('/rewards', [RewardController::class, 'index'])->middleware('auth')->name('rewards.index');
Route::post('/rewards/redeem', [RewardRedemptionController::class, 'redeem']);
