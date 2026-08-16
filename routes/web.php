<?php

use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminComplaintController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminDivisionController;
use App\Http\Controllers\Web\AdminFacilityController;
use App\Http\Controllers\Web\AdminFcmTestController;
use App\Http\Controllers\Web\AdminNotificationController;
use App\Http\Controllers\Web\AdminReportController;
use App\Http\Controllers\Web\AdminReservationDebugController;
use App\Http\Controllers\Web\AdminUserController;
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

    Route::middleware(['auth', 'admin', 'password.changed'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');

        // Force password change
        Route::get('/password/change', [AdminAuthController::class, 'showChangePassword'])->name('admin.password.change');
        Route::post('/password/change', [AdminAuthController::class, 'changePassword'])->name('admin.password.change.submit');

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

        // Division master CRUD
        Route::get('/divisions', [AdminDivisionController::class, 'index'])->name('admin.divisions');
        Route::get('/divisions/create', [AdminDivisionController::class, 'create'])->name('admin.divisions.create');
        Route::post('/divisions', [AdminDivisionController::class, 'store'])->name('admin.divisions.store');
        Route::get('/divisions/{division}/edit', [AdminDivisionController::class, 'edit'])->name('admin.divisions.edit');
        Route::put('/divisions/{division}', [AdminDivisionController::class, 'update'])->name('admin.divisions.update');
        Route::delete('/divisions/{division}', [AdminDivisionController::class, 'destroy'])->name('admin.divisions.destroy');

        // Users CRUD
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

        // Reservations CRUD
        Route::get('/reservations', [AdminDashboardController::class, 'reservations'])->name('admin.reservations');
        Route::get('/reservations/create', [AdminDashboardController::class, 'createReservation'])->name('admin.reservations.create');
        Route::post('/reservations', [AdminDashboardController::class, 'storeReservation'])->name('admin.reservations.store');
        Route::get('/reservations/{reservation}/edit', [AdminDashboardController::class, 'editReservation'])->name('admin.reservations.edit');
        Route::put('/reservations/{reservation}', [AdminDashboardController::class, 'updateReservation'])->name('admin.reservations.update');
        Route::post('/reservations/{reservation}/complete', [AdminDashboardController::class, 'completeReservation'])->name('admin.reservations.complete');
        Route::post('/reservations/{reservation}/cancel', [AdminDashboardController::class, 'cancelReservation'])->name('admin.reservations.cancel');

        // Calendar endpoints
        Route::get('/reservations/calendar/events', [AdminDashboardController::class, 'getCalendarEvents'])->name('admin.reservations.calendar.events');
        Route::patch('/reservations/{reservation}/time', [AdminDashboardController::class, 'updateReservationTime'])->name('admin.reservations.update-time');

        // User timezone
        Route::post('/set-timezone', [AdminDashboardController::class, 'setUserTimezone'])->name('admin.set-timezone');

        // Approval
        Route::get('/approvals', [AdminDashboardController::class, 'approvals'])->name('admin.approvals');
        Route::post('/approvals/{reservation}/approve', [AdminDashboardController::class, 'approveReservation'])->name('admin.approvals.approve');
        Route::post('/approvals/{reservation}/reject', [AdminDashboardController::class, 'rejectReservation'])->name('admin.approvals.reject');

        // Complaints
        Route::get('/complaints', [AdminComplaintController::class, 'index'])->name('admin.complaints');
        Route::get('/complaints/create', [AdminComplaintController::class, 'create'])->name('admin.complaints.create');
        Route::post('/complaints', [AdminComplaintController::class, 'store'])->name('admin.complaints.store');
        Route::get('/complaints/{complaint}', [AdminComplaintController::class, 'show'])->name('admin.complaints.show');
        Route::patch('/complaints/{complaint}/status', [AdminComplaintController::class, 'updateStatus'])->name('admin.complaints.update-status');

        // Reports (Outputs 3, 5, 6, 7, 8, 9, 10)
        Route::prefix('reports')->name('admin.reports.')->group(function () {
            Route::get('/complaints',        [AdminReportController::class, 'complaints'])->name('complaints');
            Route::get('/usage',             [AdminReportController::class, 'usage'])->name('usage');
            Route::get('/user-activity',     [AdminReportController::class, 'userActivity'])->name('user-activity');
            Route::get('/schedule-history',  [AdminReportController::class, 'scheduleHistory'])->name('schedule-history');
            Route::get('/periodic',          [AdminReportController::class, 'periodic'])->name('periodic');
            Route::get('/division-activity', [AdminReportController::class, 'divisionActivity'])->name('division-activity');
            Route::get('/maintenance',       [AdminReportController::class, 'maintenance'])->name('maintenance');
            Route::get('/division-usage',    [AdminReportController::class, 'divisionUsage'])->name('division-usage');
        });

        // Tools
        Route::get('/tools/fcm-test', [AdminFcmTestController::class, 'showForm'])->name('admin.tools.fcm-test');
        Route::post('/tools/fcm-test', [AdminFcmTestController::class, 'send'])->name('admin.tools.fcm-test.send');

        // Debug Feature
        Route::get('/tools/reservation-debug', [AdminReservationDebugController::class, 'index'])->name('admin.tools.reservation-debug');
        Route::post('/tools/reservation-debug/run', [AdminReservationDebugController::class, 'runTransition'])->name('admin.tools.reservation-debug.run');
        Route::post('/tools/reservation-debug/{reservation}/backdate', [AdminReservationDebugController::class, 'backdate'])->name('admin.tools.reservation-debug.backdate');

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications');
        Route::get('/notifications/unread', [AdminNotificationController::class, 'unread'])->name('admin.notifications.unread');
        Route::post('/notifications/{id}/read', [AdminNotificationController::class, 'markRead'])->name('admin.notifications.read');
        Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');

        Route::match(['get', 'post'], '/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});
