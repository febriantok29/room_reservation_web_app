<?php

namespace App\Services;

use App\Support\ApiMessages;
use App\Support\OperatingHours;
use App\Support\ReservationStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use InvalidArgumentException;

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
            throw new InvalidArgumentException(ApiMessages::RESERVATION_CONSTRAINT_START_BEFORE_END);
        }

        // Check for conflicts using CSP constraint checking
        // A conflict exists if there's any overlap with existing reservations
        // Overlap occurs when: (start1 < end2) AND (start2 < end1)
        $query = DB::table('t_reservations')
            ->where('room_id', $roomId)
            ->whereNull('deleted_at')
            ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Approved->value]) // Only check active reservations
            ->where(function ($q) use ($startTime, $endTime) {
                // Standard half-open interval overlap: [existingStart, existingEnd) ∩ [newStart, newEnd) ≠ ∅
                // Equivalent to: existingStart < newEnd AND existingEnd > newStart
                // Back-to-back reservations (A ends at 10:00, B starts at 10:00) are NOT blocked.
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
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
            ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Approved->value])
            ->where(function ($q) use ($startTime, $endTime) {
                // Standard half-open interval overlap
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
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
            $errors[] = ApiMessages::RESERVATION_CONSTRAINT_START_BEFORE_END;
        }

        // Constraint 2: Cannot book in the past
        if ($startTime->lt(now())) {
            $errors[] = ApiMessages::RESERVATION_CONSTRAINT_PAST_TIME;
        }

        // Constraint 2a & 2b evaluate weekday/operating-hours in local (WIB) wall-clock time.
        // Stored/returned values stay UTC — ->copy() leaves $startTime/$endTime untouched.
        $tz = config('app.timezone_user', 'Asia/Jakarta');
        $startLocal = $startTime->copy()->timezone($tz);
        $endLocal   = $endTime->copy()->timezone($tz);

        // Constraint 2a: Only weekdays (Monday-Friday)
        if ($startLocal->isWeekend()) {
            $errors[] = ApiMessages::RESERVATION_CONSTRAINT_WEEKEND;
        }

        // Constraint 2b: Within operating hours 08:00-17:00 (compare minute-of-day)
        // ponytail: assumes same-day reservation (no midnight crossing); add $startLocal->isSameDay($endLocal) if needed.
        $startMin = $startLocal->hour * 60 + $startLocal->minute;
        $endMin   = $endLocal->hour * 60 + $endLocal->minute;
        if ($startMin < OperatingHours::START_HOUR * 60 || $endMin > OperatingHours::END_HOUR * 60) {
            $errors[] = ApiMessages::RESERVATION_CONSTRAINT_OUTSIDE_HOURS;
        }

        // Constraint 3: Check if room exists and is not deleted
        $room = DB::table('m_rooms')
            ->where('id', $roomId)
            ->whereNull('deleted_at')
            ->first();

        if (!$room) {
            $errors[] = ApiMessages::RESERVATION_CONSTRAINT_ROOM_NOT_FOUND;
            return ['valid' => false, 'errors' => $errors];
        }

        // Constraint 4: Room must not be in maintenance
        if ($this->isRoomInMaintenance($roomId)) {
            $errors[] = ApiMessages::RESERVATION_CONSTRAINT_ROOM_MAINTENANCE;
        }

        // Constraint 5: Visitor count must not exceed room capacity
        if ($visitorCount > $room->capacity) {
            $errors[] = ApiMessages::RESERVATION_CONSTRAINT_CAPACITY_EXCEEDED;
        }

        // Constraint 6: No time conflicts (CSP main constraint)
        if (!$this->isRoomAvailable($roomId, $startTime, $endTime, $excludeReservationId)) {
            $conflicts = $this->getConflictingReservations($roomId, $startTime, $endTime, $excludeReservationId);
            $ids = array_values(array_filter(array_map(fn ($c) => $c->id ?? null, $conflicts)));
            $message = ApiMessages::RESERVATION_CONSTRAINT_SLOT_UNAVAILABLE;
            if (!empty($ids)) {
                $message .= ' (Reservasi bentrok: ' . implode(', ', $ids) . ')';
            }
            $errors[] = $message;
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
        $startOfDay = $date->copy()->setTime(OperatingHours::START_HOUR, 0);
        $endOfDay = $date->copy()->setTime(OperatingHours::END_HOUR, 0);

        $availableSlots = [];
        $current = $startOfDay->copy();
        $now = now();

        while ($current->lt($endOfDay)) {
            $slotEnd = $current->copy()->addMinutes($intervalMinutes);

            // Skip slots that have already passed — they cannot be booked
            if ($slotEnd->lte($now)) {
                $current->addMinutes($intervalMinutes);
                continue;
            }

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
