<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\EventTypeController;
use App\Http\Controllers\Api\V1\ReservationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class,'logout']);
    Route::get('events/{event}/reservations', [ReservationController::class, 'eventReservations']);
    Route::get('events/{event}/check-reservation', [ReservationController::class, 'checkReservation']);
    Route::get('popular-events', [ReservationController::class, 'popularEvents']);

    Route::apiResource('locations', LocationController::class);
    Route::apiResource('event-types', EventTypeController::class);
    Route::apiResource('events', EventController::class);
    Route::apiResource('reservations', ReservationController::class);
    Route::apiResource('users', UserController::class);

});
