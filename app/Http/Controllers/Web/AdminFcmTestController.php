<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TestPush;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminFcmTestController extends Controller
{
    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function showForm(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $users = User::query()
            ->has('fcmTokens')
            ->withCount('fcmTokens')
            ->orderBy('first_name')
            ->get();

        return view('admin.tools.fcm-test', compact('users'));
    }

    public function send(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'target'      => 'required|in:user,all',
            'user_id'     => 'required_if:target,user|nullable|string|exists:s_users,id',
            'title'       => 'required|string|max:100',
            'body'        => 'required|string|max:500',
            'to_database' => 'nullable|boolean',
        ], [
            'target.required'      => 'Target pengiriman wajib dipilih.',
            'user_id.required_if'  => 'User tujuan wajib dipilih.',
            'title.required'       => 'Judul wajib diisi.',
            'body.required'        => 'Isi pesan wajib diisi.',
        ]);

        $recipients = $validated['target'] === 'all'
            ? User::query()->has('fcmTokens')->withCount('fcmTokens')->get()
            : User::query()->where('id', $validated['user_id'])->withCount('fcmTokens')->get();

        if ($recipients->isEmpty() || $recipients->sum('fcm_tokens_count') === 0) {
            return back()->withErrors(['user_id' => 'Tidak ada device dengan token FCM untuk target ini.'])->withInput();
        }

        $notification = new TestPush(
            $validated['title'],
            $validated['body'],
            $request->boolean('to_database'),
        );

        try {
            foreach ($recipients as $user) {
                $user->notify($notification);
            }
        } catch (Throwable $e) {
            return back()
                ->withErrors(['send' => 'Gagal mengirim FCM: ' . $e->getMessage()])
                ->withInput();
        }

        $deviceCount = $recipients->sum('fcm_tokens_count');

        return back()->with('success', "Notifikasi test dikirim ke {$recipients->count()} user ({$deviceCount} device).");
    }
}
