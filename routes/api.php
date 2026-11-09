<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\PhotoController;
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

Route::get('listings', [ListingController::class, 'index']);
Route::get('listings/mine', [ListingController::class, 'mine'])
    ->middleware('auth:sanctum');
Route::get('listings/{listing}', [ListingController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::put('auth/me', [AuthController::class, 'updateProfile']);

    Route::post('listings', [ListingController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::post('photos', [PhotoController::class, 'store'])
        ->middleware('throttle:30,1');
    Route::put('listings/{listing}', [ListingController::class, 'update']);
    Route::patch('listings/{listing}/sold', [ListingController::class, 'markSold']);
    Route::delete('listings/{listing}', [ListingController::class, 'destroy']);
});
