<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReservationIdGenerator
{
    /**
     * Generate unique reservation ID with format: RSV-YYYYMMDD-XX
     *
     * @param string $date Date in Y-m-d format (default: today)
     * @return string Generated reservation ID
     */
    public static function generate(string $date = null): string
    {
        $date = $date ?? now()->format('Y-m-d');
        $formattedDate = str_replace('-', '', $date); // YYYYMMDD

        // Get the last reservation for today
        $lastReservation = DB::table('t_reservations')
            ->where('id', 'LIKE', "RSV-{$formattedDate}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReservation) {
            // Extract the increment number from last reservation
            $lastIncrement = (int) substr($lastReservation->id, -2);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        // Format increment with leading zeros (max 99, or can extend to 999)
        $incrementFormatted = str_pad($newIncrement, 2, '0', STR_PAD_LEFT);

        return "RSV-{$formattedDate}-{$incrementFormatted}";
    }
}
