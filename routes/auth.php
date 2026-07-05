<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationOtpController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpPasswordResetController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('register/verify-otp', [EmailVerificationOtpController::class, 'showVerifyForm'])
        ->name('register.otp.verify');

    Route::post('register/verify-otp', [EmailVerificationOtpController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('register.otp.verify.store');

    Route::post('register/resend-otp', [EmailVerificationOtpController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('register.otp.resend');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('forgot-password/verify-otp', [OtpPasswordResetController::class, 'showVerifyForm'])
        ->name('password.otp.verify');

    Route::post('forgot-password/verify-otp', [OtpPasswordResetController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('password.otp.verify.store');

    Route::get('forgot-password/reset', [OtpPasswordResetController::class, 'showResetForm'])
        ->name('password.otp.reset');

    Route::post('forgot-password/reset', [OtpPasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:6,1')
        ->name('password.otp.reset.store');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

// Logout must accept either guard — customers authenticate via the 'customer' guard,
// which the bare 'auth' middleware above (default 'staff' guard) never recognizes,
// so a customer's session was never actually being invalidated on logout.
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:staff,customer')
    ->name('logout');
