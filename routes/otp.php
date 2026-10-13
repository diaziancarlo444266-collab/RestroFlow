<?php

use App\Http\Controllers\EmailOtpController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/email-otp', [EmailOtpController::class, 'show'])->name('otp.show');
    Route::post('/email-otp', [EmailOtpController::class, 'verify'])->middleware('throttle:10,1')->name('otp.verify');
    Route::post('/email-otp/resend', [EmailOtpController::class, 'resend'])->middleware('throttle:5,1')->name('otp.resend');
    Route::post('/email-otp/cancel', [EmailOtpController::class, 'logout'])->name('otp.cancel');
});
