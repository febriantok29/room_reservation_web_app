<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * List all notifications for the authenticated user.
     * Pass ?unread=1 to filter only unread ones.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->notifications()
            ->select('id', 'type', 'data', 'read_at', 'created_at');

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $paginated = $query->paginate($perPage);

        // Shape the data so mobile doesn't have to dig into nested 'data' key
        $items = collect($paginated->items())->map(fn ($n) => $this->format($n));

        return ApiResponse::success(
            $items,
            ApiMessages::NOTIFICATION_LIST_SUCCESS,
            200,
            [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]
        );
    }

    /**
     * Get unread notification count only (for badge).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ['unread_count' => $request->user()->unreadNotifications()->count()],
            ApiMessages::NOTIFICATION_UNREAD_COUNT_SUCCESS
        );
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->find($id);

        if (!$notification) {
            return ApiResponse::error(
                ApiErrorCodes::NOT_FOUND,
                ApiMessages::NOTIFICATION_NOT_FOUND,
                404
            );
        }

        $notification->markAsRead();

        return ApiResponse::success(
            $this->format($notification),
            ApiMessages::NOTIFICATION_MARKED_READ
        );
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return ApiResponse::success(null, ApiMessages::NOTIFICATION_ALL_MARKED_READ);
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = $request->user()
            ->notifications()
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return ApiResponse::error(
                ApiErrorCodes::NOT_FOUND,
                ApiMessages::NOTIFICATION_NOT_FOUND,
                404
            );
        }

        return ApiResponse::success(null, ApiMessages::NOTIFICATION_DELETED);
    }

    /**
     * Format a DatabaseNotification into a flat, mobile-friendly shape.
     */
    private function format(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        return [
            'id'             => $notification->id,
            'type'           => $data['type'] ?? null,
            'title'          => $data['title'] ?? null,
            'body'           => $data['body'] ?? null,
            'reservation_id' => $data['reservation_id'] ?? null,
            'room_name'      => $data['room_name'] ?? null,
            'status'         => $data['status'] ?? null,
            'start_time'     => $data['start_time'] ?? null,
            'end_time'       => $data['end_time'] ?? null,
            'is_read'        => $notification->read_at !== null,
            'read_at'        => $notification->read_at?->toIso8601String(),
            'created_at'     => $notification->created_at->toIso8601String(),
        ];
    }
}
