<?php

use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminFacilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()?->canApprove()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('admin.login');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');

        // Rooms CRUD
        Route::get('/rooms', [AdminDashboardController::class, 'rooms'])->name('admin.rooms');
        Route::get('/rooms/create', [AdminDashboardController::class, 'createRoom'])->name('admin.rooms.create');
        Route::post('/rooms', [AdminDashboardController::class, 'storeRoom'])->name('admin.rooms.store');
        Route::get('/rooms/{room}/edit', [AdminDashboardController::class, 'editRoom'])->name('admin.rooms.edit');
        Route::put('/rooms/{room}', [AdminDashboardController::class, 'updateRoom'])->name('admin.rooms.update');
        Route::delete('/rooms/{room}', [AdminDashboardController::class, 'destroyRoom'])->name('admin.rooms.destroy');
        Route::delete('/rooms/{room}/image', [AdminDashboardController::class, 'destroyRoomImage'])->name('admin.rooms.destroy-image');

        // Facility master CRUD
        Route::get('/facilities', [AdminFacilityController::class, 'index'])->name('admin.facilities');
        Route::get('/facilities/create', [AdminFacilityController::class, 'create'])->name('admin.facilities.create');
        Route::post('/facilities', [AdminFacilityController::class, 'store'])->name('admin.facilities.store');
        Route::get('/facilities/{facility}/edit', [AdminFacilityController::class, 'edit'])->name('admin.facilities.edit');
        Route::put('/facilities/{facility}', [AdminFacilityController::class, 'update'])->name('admin.facilities.update');
        Route::delete('/facilities/{facility}', [AdminFacilityController::class, 'destroy'])->name('admin.facilities.destroy');

        // Reservations CRUD
        Route::get('/reservations', [AdminDashboardController::class, 'reservations'])->name('admin.reservations');
        Route::get('/reservations/create', [AdminDashboardController::class, 'createReservation'])->name('admin.reservations.create');
        Route::post('/reservations', [AdminDashboardController::class, 'storeReservation'])->name('admin.reservations.store');
        Route::get('/reservations/{reservation}/edit', [AdminDashboardController::class, 'editReservation'])->name('admin.reservations.edit');
        Route::put('/reservations/{reservation}', [AdminDashboardController::class, 'updateReservation'])->name('admin.reservations.update');
        Route::delete('/reservations/{reservation}', [AdminDashboardController::class, 'destroyReservation'])->name('admin.reservations.destroy');
        Route::post('/reservations/{reservation}/complete', [AdminDashboardController::class, 'completeReservation'])->name('admin.reservations.complete');

        // User timezone
        Route::post('/set-timezone', [AdminDashboardController::class, 'setUserTimezone'])->name('admin.set-timezone');

        // Approval
        Route::get('/approvals', [AdminDashboardController::class, 'approvals'])->name('admin.approvals');
        Route::post('/approvals/{reservation}/approve', [AdminDashboardController::class, 'approveReservation'])->name('admin.approvals.approve');
        Route::post('/approvals/{reservation}/reject', [AdminDashboardController::class, 'rejectReservation'])->name('admin.approvals.reject');

        Route::match(['get', 'post'], '/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});
