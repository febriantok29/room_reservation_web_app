<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class TestPush extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $body,
        private readonly bool $toDatabase = false,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->fcmTokens()->exists()) {
            $channels[] = FcmChannel::class;
        }

        if ($this->toDatabase) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'test_push',
            'title' => $this->title,
            'body'  => $this->body,
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $data = $this->toArray($notifiable);

        return (new FcmMessage(notification: new FcmNotification(
            title: $data['title'],
            body: $data['body'],
        )))->data($data);
    }
}
