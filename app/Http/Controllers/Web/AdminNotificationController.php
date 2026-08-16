<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $query = $request->user()->notifications()->select('id', 'type', 'data', 'read_at', 'created_at');

        $notifications = $query->paginate(15)->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Feed untuk navbar bell (AdminLTE navbar-notification).
     * Mengembalikan label unread + HTML dropdown 5 notifikasi terbaru.
     */
    public function unread(Request $request): JsonResponse
    {
        $this->ensureAdminAccess($request);

        $unreadCount = $request->user()->unreadNotifications()->count();
        $recent = $request->user()->notifications()
            ->select('id', 'type', 'data', 'read_at', 'created_at')
            ->latest()
            ->take(5)
            ->get();

        $itemsHtml = '';
        foreach ($recent as $notification) {
            $data = $notification->data;
            $isRead = $notification->read_at !== null;
            $title = $data['title'] ?? 'Notifikasi';
            $body = $data['body'] ?? '';
            $created = $notification->created_at->diffForHumans();

            $itemsHtml .= '
            <a href="'.route('admin.notifications').'" class="dropdown-item'.($isRead ? '' : ' bg-light').'">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-truncate">'.e($title).'</span>
                    '.($isRead ? '' : '<span class="badge badge-primary ml-1">Baru</span>').'
                </div>
                <div class="small text-muted text-truncate">'.e($body).'</div>
                <div class="small text-muted">'.$created.'</div>
            </a>';
        }

        if ($itemsHtml === '') {
            $itemsHtml = '<div class="text-center text-muted py-3 small">Belum ada notifikasi.</div>';
        }

        return response()->json([
            'label'       => $unreadCount > 0 ? $unreadCount : null,
            'label_color' => $unreadCount > 0 ? 'danger' : null,
            'dropdown'    => $itemsHtml,
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $this->ensureAdminAccess($request);

        $notification = $request->user()->notifications()->find($id);

        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->ensureAdminAccess($request);

        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
