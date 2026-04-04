<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class ReservationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Reservation $reservation) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $room = $this->reservation->room;
        return [
            'type'           => 'reservation_completed',
            'title'          => 'Reservasi Selesai',
            'body'           => "Reservasi ruang {$room->name} pada {$this->reservation->start_time_local->format('d M Y H:i')} telah selesai.",
            'reservation_id' => $this->reservation->id,
            'room_name'      => $room->name,
            'status'         => $this->reservation->status,
            'start_time'     => $this->reservation->start_time->toIso8601String(),
            'end_time'       => $this->reservation->end_time->toIso8601String(),
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
