<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('track/{reference}', [TrackingController::class, 'show'])
    ->name('tracking.show');

Route::get('quote', [QuoteController::class, 'create'])
    ->name('quote.create');

Route::post('quote', [QuoteController::class, 'calculate'])
    ->name('quote.calculate');

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
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('shipments', [ShipmentController::class, 'index'])
        ->name('shipments.index');

    Route::get('shipments/create', [ShipmentController::class, 'create'])
        ->name('shipments.create');

    Route::post('shipments', [ShipmentController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('shipments.store');

    Route::get('shipments/export', [ShipmentController::class, 'export'])
        ->name('shipments.export');

    Route::get('shipments/{shipment}', [ShipmentController::class, 'show'])
        ->name('shipments.show');

    Route::get('shipments/{shipment}/edit', [ShipmentController::class, 'edit'])
        ->name('shipments.edit');

    Route::put('shipments/{shipment}', [ShipmentController::class, 'update'])
        ->name('shipments.update');

    Route::patch('shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])
        ->name('shipments.status');

    Route::delete('shipments/{shipment}', [ShipmentController::class, 'destroy'])
        ->name('shipments.destroy');

    Route::get('shipments/{shipment}/invoice', [ShipmentController::class, 'invoice'])
        ->name('shipments.invoice');

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
