<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RoomIdGenerator
{
    /**
     * Generate unique room ID with format: RM-LLXX
     * LL = 2-digit floor number, XX = 2-digit increment per floor.
     */
    public static function generate(int|string $floor): string
    {
        $floorNumber = self::normalizeFloorNumber($floor);
        $floorPrefix = str_pad((string) $floorNumber, 2, '0', STR_PAD_LEFT);

        $lastRoom = DB::table('m_rooms')
            ->where('id', 'LIKE', "RM-{$floorPrefix}%")
            ->orderByDesc('id')
            ->first();

        if ($lastRoom) {
            $lastIncrement = (int) substr($lastRoom->id, -2);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        if ($newIncrement > 99) {
            throw new \RuntimeException('Room ID sequence limit reached for this floor (max 99).');
        }

        $incrementFormatted = str_pad((string) $newIncrement, 2, '0', STR_PAD_LEFT);

        return "RM-{$floorPrefix}{$incrementFormatted}";
    }

    private static function normalizeFloorNumber(int|string $floor): int
    {
        if (is_int($floor)) {
            $floorNumber = $floor;
        } else {
            $cleaned = trim((string) $floor);
            if ($cleaned === '') {
                throw new \InvalidArgumentException('Floor is required to generate room ID.');
            }

            if (preg_match('/(\d+)/', $cleaned, $matches) !== 1) {
                throw new \InvalidArgumentException('Floor must contain a valid number.');
            }

            $floorNumber = (int) $matches[1];
        }

        if ($floorNumber < 1 || $floorNumber > 99) {
            throw new \InvalidArgumentException('Floor must be between 1 and 99.');
        }

        return $floorNumber;
    }
}
