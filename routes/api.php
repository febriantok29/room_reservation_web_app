<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\RoomImageController;
use App\Http\Controllers\Api\UserController;
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
    Route::post('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Room endpoints
    Route::get('/rooms/available', [RoomController::class, 'available']); // MUST be before /rooms/{id}
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{id}', [RoomController::class, 'show']);
    Route::put('/rooms/{id}', [RoomController::class, 'update']);
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);
    Route::get('/rooms/{id}/availability', [RoomController::class, 'availability']);

    // Room image endpoints (separate from room CRUD — multipart only)
    Route::post('/rooms/{id}/image', [RoomImageController::class, 'store']);
    Route::delete('/rooms/{id}/image', [RoomImageController::class, 'destroy']);

    // Facility master endpoints
    Route::get('/facilities', [FacilityController::class, 'index']);
    Route::post('/facilities', [FacilityController::class, 'store']);
    Route::get('/facilities/{id}', [FacilityController::class, 'show']);
    Route::put('/facilities/{id}', [FacilityController::class, 'update']);
    Route::delete('/facilities/{id}', [FacilityController::class, 'destroy']);

    // User endpoints
    Route::get('/users', [UserController::class, 'index']);

    // Division master endpoints
    Route::get('/divisions', [DivisionController::class, 'index']);
    Route::post('/divisions', [DivisionController::class, 'store']);
    Route::get('/divisions/{id}', [DivisionController::class, 'show']);
    Route::put('/divisions/{id}', [DivisionController::class, 'update']);
    Route::delete('/divisions/{id}', [DivisionController::class, 'destroy']);

    // Reservation endpoints
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/calendar', [ReservationController::class, 'calendar']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::post('/reservations/{id}/complete', [ReservationController::class, 'complete']);
    Route::post('/reservations/{id}/approve', [ReservationController::class, 'approve']);
    Route::post('/reservations/{id}/reject', [ReservationController::class, 'reject']);

    // Complaint endpoints
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/{id}', [ComplaintController::class, 'show']);
    Route::patch('/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);

    // Report endpoints (Outputs 3, 5, 6, 7, 8, 9, 10)
    Route::prefix('reports')->group(function () {
        Route::get('/complaints',        [ReportController::class, 'complaints']);
        Route::get('/usage',             [ReportController::class, 'usage']);
        Route::get('/user-activity',     [ReportController::class, 'userActivity']);
        Route::get('/schedule-history',  [ReportController::class, 'scheduleHistory']);
        Route::get('/periodic',          [ReportController::class, 'periodic']);
        Route::get('/division-activity', [ReportController::class, 'divisionActivity']);
        Route::get('/maintenance',       [ReportController::class, 'maintenance']);
        Route::get('/division-usage',    [ReportController::class, 'divisionUsage']);
    });

    // Notification history endpoints
    Route::prefix('notifications')->group(function () {
        Route::get('/',                            [NotificationController::class, 'index']);
        Route::get('/unread-count',                [NotificationController::class, 'unreadCount']);
        Route::post('/read-all',                   [NotificationController::class, 'markAllRead']);
        Route::post('/{id}/read',                  [NotificationController::class, 'markRead']);
        Route::delete('/{id}',                     [NotificationController::class, 'destroy']);
    });
});
