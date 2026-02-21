<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Constraint Satisfaction Problem (CSP) Service for Room Reservations
 *
 * This service implements CSP algorithm to ensure no time conflicts
 * occur when making room reservations. It checks constraints before
 * allowing a new reservation to be created.
 */
class CSPService
{
    /**
     * Check if a room is available for the given time slot
     *
    * @param string $roomId Room ID to check (UUID string)
     * @param Carbon|string $startTime Start time of desired reservation
     * @param Carbon|string $endTime End time of desired reservation
     * @param string|null $excludeReservationId Reservation ID to exclude from check (for updates)
     * @return bool True if available, false if conflict exists
     */
    public function isRoomAvailable(
        string $roomId,
        $startTime,
        $endTime,
        ?string $excludeReservationId = null
    ): bool {
        $startTime = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
        $endTime = $endTime instanceof Carbon ? $endTime : Carbon::parse($endTime);

        // Validate time range
        if ($startTime->gte($endTime)) {
            throw new \InvalidArgumentException('Start time must be before end time');
        }

        // Check for conflicts using CSP constraint checking
        // A conflict exists if there's any overlap with existing reservations
        // Overlap occurs when: (start1 < end2) AND (start2 < end1)
        $query = DB::table('t_reservations')
            ->where('room_id', $roomId)
            ->whereNull('deleted_at')
            ->whereIn('status', ['pending', 'approved']) // Only check active reservations
            ->where(function ($q) use ($startTime, $endTime) {
                // Check for time overlap
                $q->where(function ($subQ) use ($startTime, $endTime) {
                    // New reservation starts during existing reservation
                    $subQ->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    // New reservation ends during existing reservation
                    $subQ->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    // New reservation completely contains existing reservation
                    $subQ->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    // Existing reservation completely contains new reservation
                    $subQ->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            });

        // Exclude specific reservation ID if provided (for update operations)
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        $conflicts = $query->count();

        return $conflicts === 0;
    }

    /**
     * Check if room is in maintenance mode
     *
    * @param string $roomId Room ID to check (UUID string)
     * @return bool True if in maintenance, false otherwise
     */
    public function isRoomInMaintenance(string $roomId): bool
    {
        return DB::table('m_rooms')
            ->where('id', $roomId)
            ->whereNull('deleted_at')
            ->where('is_maintenance', true)
            ->exists();
    }

    /**
     * Get all conflicting reservations for a given time slot
     *
    * @param string $roomId Room ID to check (UUID string)
     * @param Carbon|string $startTime Start time
     * @param Carbon|string $endTime End time
     * @param string|null $excludeReservationId Reservation ID to exclude
     * @return array Array of conflicting reservations
     */
    public function getConflictingReservations(
        string $roomId,
        $startTime,
        $endTime,
        ?string $excludeReservationId = null
    ): array {
        $startTime = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
        $endTime = $endTime instanceof Carbon ? $endTime : Carbon::parse($endTime);

        $query = DB::table('t_reservations')
            ->where('room_id', $roomId)
            ->whereNull('deleted_at')
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($subQ) use ($startTime, $endTime) {
                    $subQ->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    $subQ->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    $subQ->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    $subQ->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->get()->toArray();
    }

    /**
     * Validate all constraints for a new reservation
     *
     * @param string $roomId Room ID (ULID string)
     * @param Carbon|string $startTime Start time
     * @param Carbon|string $endTime End time
     * @param int $visitorCount Number of visitors
     * @param string|null $excludeReservationId Reservation ID to exclude (for updates)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateReservation(
        string $roomId,
        $startTime,
        $endTime,
        int $visitorCount = 1,
        ?string $excludeReservationId = null
    ): array {
        $errors = [];

        $startTime = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
        $endTime = $endTime instanceof Carbon ? $endTime : Carbon::parse($endTime);

        // Constraint 1: Start time must be before end time
        if ($startTime->gte($endTime)) {
            $errors[] = 'Start time must be before end time';
        }

        // Constraint 2: Cannot book in the past
        if ($startTime->lt(now())) {
            $errors[] = 'Cannot make reservations for past time';
        }

        // Constraint 3: Check if room exists and is not deleted
        $room = DB::table('m_rooms')
            ->where('id', $roomId)
            ->whereNull('deleted_at')
            ->first();

        if (!$room) {
            $errors[] = 'Room does not exist or has been deleted';
            return ['valid' => false, 'errors' => $errors];
        }

        // Constraint 4: Room must not be in maintenance
        if ($this->isRoomInMaintenance($roomId)) {
            $errors[] = 'Room is currently under maintenance';
        }

        // Constraint 5: Visitor count must not exceed room capacity
        if ($visitorCount > $room->capacity) {
            $errors[] = "Visitor count ({$visitorCount}) exceeds room capacity ({$room->capacity})";
        }

        // Constraint 6: No time conflicts (CSP main constraint)
        if (!$this->isRoomAvailable($roomId, $startTime, $endTime, $excludeReservationId)) {
            $errors[] = 'Time slot is not available. Conflict with existing reservation(s)';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Get available time slots for a room on a specific date
     *
     * @param string $roomId Room ID (ULID string)
     * @param Carbon|string $date Date to check
     * @param int $intervalMinutes Interval in minutes (default: 30)
     * @return array Available time slots
     */
    public function getAvailableTimeSlots(
        string $roomId,
        $date,
        int $intervalMinutes = 30
    ): array {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $startOfDay = $date->copy()->setTime(8, 0); // Start at 8 AM
        $endOfDay = $date->copy()->setTime(18, 0); // End at 6 PM

        $availableSlots = [];
        $current = $startOfDay->copy();

        while ($current->lt($endOfDay)) {
            $slotEnd = $current->copy()->addMinutes($intervalMinutes);

            if ($this->isRoomAvailable($roomId, $current, $slotEnd)) {
                $availableSlots[] = [
                    'start' => $current->toIso8601String(),
                    'end' => $slotEnd->toIso8601String(),
                ];
            }

            $current->addMinutes($intervalMinutes);
        }

        return $availableSlots;
    }
}
