<?php

namespace App\Listeners;

use App\Models\FcmToken;
use Illuminate\Notifications\Events\NotificationFailed;

class DeleteExpiredNotificationTokens
{
    /**
     * Hapus token FCM yang sudah tidak valid (NotRegistered / InvalidRegistration)
     * agar push notification tidak terus dikirim ke token basi.
     */
    public function handle(NotificationFailed $event): void
    {
        $report = $event->data['report'] ?? null;

        if (! $report) {
            return;
        }

        // Kumpulkan token yang gagal karena sudah tidak terdaftar di FCM.
        $tokensToDelete = [];

        if (method_exists($report, 'getItems')) {
            foreach ($report->getItems() as $item) {
                if (method_exists($item, 'isFailure') && $item->isFailure()) {
                    $reason = (string) $item->error();
                    if (str_contains($reason, 'NotRegistered')
                        || str_contains($reason, 'InvalidRegistration')
                        || str_contains($reason, 'Registration token')) {
                        $tokensToDelete[] = $item->token();
                    }
                }
            }
        } elseif (method_exists($report, 'token') && method_exists($report, 'isFailure') && $report->isFailure()) {
            $reason = (string) $report->error();
            if (str_contains($reason, 'NotRegistered')
                || str_contains($reason, 'InvalidRegistration')
                || str_contains($reason, 'Registration token')) {
                $tokensToDelete[] = $report->token();
            }
        }

        if (empty($tokensToDelete)) {
            return;
        }

        foreach (array_unique($tokensToDelete) as $token) {
            FcmToken::where('token', $token)->delete();
        }
    }
}
