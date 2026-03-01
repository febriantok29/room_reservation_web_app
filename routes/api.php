<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('jwt')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Room endpoints
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{id}', [RoomController::class, 'show']);
    Route::get('/rooms/{id}/availability', [RoomController::class, 'availability']);

    // Reservation endpoints
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::post('/reservations/{id}/approve', [ReservationController::class, 'approve']);
    Route::post('/reservations/{id}/reject', [ReservationController::class, 'reject']);
});
