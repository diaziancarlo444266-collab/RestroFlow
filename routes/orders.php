<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:records.view'])->group(function () {
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'show', 'edit'])->parameters(['orders' => 'id']);
    Route::prefix('api')->name('api.')->group(function () {
        Route::apiResource('orders', OrderController::class)->parameters(['orders' => 'id'])
            ->middlewareFor('update', 'can:records.update')->middlewareFor('destroy', 'can:records.delete');
    });
});
