<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderPaymentController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SavedSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DECLUTTER NG marketplace API
|--------------------------------------------------------------------------
| Consumed by the static prototype front-end (declutter-ng/). Auth uses
| Sanctum bearer tokens; listings support the same filters as the
| prototype's Store.getItems().
*/

Route::post('auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:10,1');

Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

Route::post('auth/verify-email', [AuthController::class, 'verifyEmail'])
    ->middleware('throttle:20,1');

Route::post('webhooks/paystack', [OrderPaymentController::class, 'webhook'])
    ->middleware('throttle:60,1');

Route::get('listings', [ListingController::class, 'index']);
Route::get('listings/mine', [ListingController::class, 'mine'])
    ->middleware('auth:sanctum');
Route::get('listings/{listing}', [ListingController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::put('auth/me', [AuthController::class, 'updateProfile']);
    Route::post('auth/email/verification-notification', [AuthController::class, 'sendVerification']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);

    Route::post('listings', [ListingController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::post('photos', [PhotoController::class, 'store'])
        ->middleware('throttle:30,1');
    Route::put('listings/{listing}', [ListingController::class, 'update']);
    Route::patch('listings/{listing}/sold', [ListingController::class, 'markSold']);
    Route::delete('listings/{listing}', [ListingController::class, 'destroy']);

    Route::post('offers', [OfferController::class, 'store'])
        ->middleware('throttle:30,1');
    Route::get('offers/made', [OfferController::class, 'made']);
    Route::get('offers/received', [OfferController::class, 'received']);
    Route::post('offers/{offer}/accept', [OfferController::class, 'accept']);
    Route::post('offers/{offer}/decline', [OfferController::class, 'decline']);
    Route::post('offers/{offer}/counter', [OfferController::class, 'counter']);
    Route::post('offers/{offer}/cancel', [OfferController::class, 'cancel']);
    Route::post('offers/{offer}/accept-counter', [OfferController::class, 'acceptCounter']);

    Route::post('reviews', [ReviewController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::get('reviews/user/{userId}', [ReviewController::class, 'forUser']);
    Route::get('reviews/order/{order}', [ReviewController::class, 'forOrder']);

    Route::post('orders', [OrderController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/sales', [OrderController::class, 'sales']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('orders/{order}/pay', [OrderPaymentController::class, 'initialize'])
        ->middleware('throttle:10,1');
    Route::post('orders/{order}/verify-payment', [OrderPaymentController::class, 'verify'])
        ->middleware('throttle:20,1');
    Route::post('orders/{order}/dispute', [DisputeController::class, 'store'])
        ->middleware('throttle:10,1');

    Route::get('disputes', [DisputeController::class, 'index']);

    Route::get('saved-searches', [SavedSearchController::class, 'index']);
    Route::post('saved-searches', [SavedSearchController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::delete('saved-searches/{savedSearch}', [SavedSearchController::class, 'destroy']);
    Route::post('disputes/{dispute}/close', [DisputeController::class, 'close']);
});
