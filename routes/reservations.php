<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:records.view'])->group(function () {
    Route::resource('reservations', ReservationController::class)->only(['index', 'create', 'show', 'edit'])->parameters(['reservations' => 'id']);
    Route::prefix('api')->name('api.')->group(function () {
        Route::apiResource('reservations', ReservationController::class)->parameters(['reservations' => 'id'])
            ->middlewareFor('update', 'can:records.update')->middlewareFor('destroy', 'can:records.delete');
    });
});
