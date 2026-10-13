<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:records.view'])->group(function () {
    Route::resource('inventory', InventoryController::class)->only(['index', 'create', 'show', 'edit'])->parameters(['inventory' => 'id']);
    Route::prefix('api')->name('api.')->group(function () {
        Route::apiResource('inventory', InventoryController::class)->parameters(['inventory' => 'id'])
            ->middlewareFor('update', 'can:records.update')->middlewareFor('destroy', 'can:records.delete');
    });
});
