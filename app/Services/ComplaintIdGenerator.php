<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ComplaintIdGenerator
{
    /**
     * Generate unique complaint ID with format: CMP-YYYYMMDD-XXX
     *
     * @param  string|null  $date  Date in Y-m-d format (default: today)
     * @return string Generated complaint ID
     */
    public static function generate(?string $date = null): string
    {
        $date = $date ?? now()->format('Y-m-d');
        $formattedDate = str_replace('-', '', $date); // YYYYMMDD

        $last = DB::table('t_room_complaints')
            ->where('id', 'LIKE', "CMP-{$formattedDate}-%")
            ->orderBy('id', 'desc')
            ->first();

        $newIncrement = $last ? ((int) substr($last->id, -3)) + 1 : 1;
        $incrementFormatted = str_pad($newIncrement, 3, '0', STR_PAD_LEFT);

        return "CMP-{$formattedDate}-{$incrementFormatted}";
    }
}
