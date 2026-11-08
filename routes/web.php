<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\VehicleController;
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

Route::get('blog', [PostController::class, 'index'])
    ->name('blog.index');

Route::get('blog/{slug}', [PostController::class, 'show'])
    ->name('blog.show');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');

    Route::get('register/verify', [RegisteredUserController::class, 'verifyForm'])
        ->name('register.verify');

    Route::post('register/verify', [RegisteredUserController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('register.verify.store');

    Route::post('register/resend', [RegisteredUserController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('register.resend');

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

    Route::post('shipments/{shipment}/pay', [PaymentController::class, 'store'])
        ->name('shipments.pay');

    Route::get('payments/callback', [PaymentController::class, 'callback'])
        ->name('payments.callback');

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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('shipments', [AdminController::class, 'shipments'])->name('shipments');
    Route::patch('shipments/{shipment}/status', [AdminController::class, 'updateShipmentStatus'])->name('shipments.status');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::patch('users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');

    Route::resource('vehicles', VehicleController::class)->except(['show']);

    Route::get('posts', [PostController::class, 'adminIndex'])->name('posts.index');
    Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});
