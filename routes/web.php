<?php

use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::pattern('id', '[0-9]+');

require __DIR__.'/otp.php';

Route::get('/', [RestaurantController::class, 'index'])->middleware(['auth', 'can:records.view'])->name('dashboard');
Route::prefix('api')->middleware(['auth', 'can:records.view'])->group(function () {
    Route::get('dashboard', [RestaurantController::class, 'dashboard'])->name('api.dashboard');
    Route::get('lookups', [RestaurantController::class, 'lookups'])->name('api.lookups');
});
require __DIR__.'/orders.php';
require __DIR__.'/reservations.php';
require __DIR__.'/inventory.php';
