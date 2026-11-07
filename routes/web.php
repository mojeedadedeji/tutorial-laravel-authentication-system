<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('profile', [ProfileController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('profile.update');

    Route::get('delete-account', [ProfileController::class, 'confirmDelete'])
        ->name('account.delete');

    Route::delete('profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:6,1')
        ->name('profile.destroy');

    Route::get('password', [PasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.change');

    Route::get('payment-methods', [PaymentMethodController::class, 'index'])
        ->name('payment-methods.index');

    Route::post('payment-methods', [PaymentMethodController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('payment-methods.store');

    Route::delete('payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])
        ->name('payment-methods.destroy');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
