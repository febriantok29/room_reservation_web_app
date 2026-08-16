<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationApproved;
use App\Notifications\ReservationCancelled;
use App\Notifications\ReservationCompleted;
use App\Notifications\ReservationCreated;
use App\Notifications\ReservationRejected;
use App\Notifications\ReservationSubmitted;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use App\Support\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ReservationService
{
    private CSPService $cspService;

    public function __construct(CSPService $cspService)
    {
        $this->cspService = $cspService;
    }

    public function queryFor(User $actor): Builder
    {
        $query = Reservation::query()->with(['room', 'user', 'user.division:id,name,code']);

        if (! $actor->canApprove()) {
            $query->forUser($actor->id);
        }

        return $query;
    }

    public function canAccess(User $actor, Reservation $reservation): bool
    {
        if ($actor->canApprove()) {
            return true;
        }

        return $reservation->user_id === $actor->id;
    }

    public function create(User $actor, array $payload): array
    {
        $reservationUserId = $payload['user_id'] ?? $actor->id;
        $roomId = $payload['room_id'];
        $startTime = Carbon::parse($payload['start_time'])->utc();
        $endTime = Carbon::parse($payload['end_time'])->utc();
        $visitorCount = (int) $payload['visitor_count'];
        $isAdminCreated = $actor->canApprove();
        $initialStatus = $isAdminCreated
            ? ReservationStatus::Approved->value
            : ReservationStatus::Pending->value;

        return DB::transaction(function () use (
            $actor,
            $reservationUserId,
            $roomId,
            $startTime,
            $endTime,
            $visitorCount,
            $payload,
            $isAdminCreated,
            $initialStatus
        ) {
            DB::table('m_rooms')->where('id', $roomId)->lockForUpdate()->first();

            $constraint = $this->cspService->validateReservation(
                $roomId,
                $startTime,
                $endTime,
                $visitorCount
            );

            if (! $constraint['valid']) {
                // Log constraint errors for debugging
                Log::warning('Reservation constraint validation failed', [
                    'room_id' => $roomId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'visitor_count' => $visitorCount,
                    'constraint_errors' => $constraint['errors'] ?? [],
                ]);

                // Format errors as flat array for better frontend display
                $errorMessages = [];
                if (isset($constraint['errors']) && is_array($constraint['errors'])) {
                    foreach ($constraint['errors'] as $error) {
                        $errorMessages[] = $error;
                    }
                }

                // Use first constraint error as main message, or fallback to generic message
                $mainMessage = ! empty($errorMessages)
                    ? $errorMessages[0]
                    : ApiMessages::RESERVATION_CONSTRAINT_CREATE_FAILED;

                return [
                    'success' => false,
                    'status_code' => 422,
                    'error_code' => ApiErrorCodes::RESERVATION_CONSTRAINT_FAILED,
                    'message' => $mainMessage,
                    'errors' => [
                        'reservation' => $errorMessages ?: ['Reservasi tidak valid'],
                    ],
                ];
            }

            $reservation = Reservation::create([
                'user_id' => $reservationUserId,
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'purpose' => $payload['purpose'] ?? null,
                'visitor_count' => $visitorCount,
                'with_snack' => $payload['with_snack'] ?? false,
                'with_lunch' => $payload['with_lunch'] ?? false,
                'status' => $initialStatus,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $reservation->load(['room', 'user', 'user.division:id,name,code']);

            if ($reservation->user) {
                // Admin-created reservations are approved immediately, so notify the
                // requester with the approved notification instead of "pending".
                $notification = $isAdminCreated
                    ? new ReservationApproved($reservation)
                    : new ReservationCreated($reservation);

                $reservation->user->notify($notification);
            }

            if (! $isAdminCreated) {
                // Notify admins that a new pending reservation awaits approval.
                $admins = User::where('is_admin', true)->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new ReservationSubmitted($reservation));
                }
            }

            return [
                'success' => true,
                'data' => $reservation,
                'message' => ApiMessages::RESERVATION_CREATED_SUCCESS,
            ];
        }, 3);
    }

    public function update(User $actor, Reservation $reservation, array $payload): array
    {
        if (! $this->canAccess($actor, $reservation)) {
            return $this->forbidden();
        }

        if (! $reservation->isPending()) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::INVALID_RESERVATION_STATUS,
                'message' => ApiMessages::RESERVATION_UPDATE_PENDING_ONLY,
                'errors' => [],
            ];
        }

        if ($reservation->start_time->lte(now())) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::RESERVATION_ALREADY_STARTED,
                'message' => ApiMessages::RESERVATION_ALREADY_STARTED,
                'errors' => [],
            ];
        }

        $roomId = $payload['room_id'] ?? $reservation->room_id;
        $userId = $payload['user_id'] ?? $reservation->user_id;
        $startTime = isset($payload['start_time'])
            ? Carbon::parse($payload['start_time'])->utc()
            : Carbon::parse($reservation->start_time)->utc();
        $endTime = isset($payload['end_time'])
            ? Carbon::parse($payload['end_time'])->utc()
            : Carbon::parse($reservation->end_time)->utc();
        $visitorCount = (int) ($payload['visitor_count'] ?? $reservation->visitor_count);

        return DB::transaction(function () use (
            $actor,
            $reservation,
            $roomId,
            $userId,
            $startTime,
            $endTime,
            $visitorCount,
            $payload
        ) {
            DB::table('m_rooms')->where('id', $roomId)->lockForUpdate()->first();

            $constraint = $this->cspService->validateReservation(
                $roomId,
                $startTime,
                $endTime,
                $visitorCount,
                $reservation->id
            );

            if (! $constraint['valid']) {
                return [
                    'success' => false,
                    'status_code' => 422,
                    'error_code' => ApiErrorCodes::RESERVATION_CONSTRAINT_FAILED,
                    'message' => ApiMessages::RESERVATION_CONSTRAINT_UPDATE_FAILED,
                    'errors' => ['constraints' => $constraint['errors']],
                ];
            }

            $reservation->fill([
                'user_id' => $userId,
                'room_id' => $roomId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'purpose' => $payload['purpose'] ?? $reservation->purpose,
                'visitor_count' => $visitorCount,
                'with_snack' => $payload['with_snack'] ?? $reservation->with_snack,
                'with_lunch' => $payload['with_lunch'] ?? $reservation->with_lunch,
                'updated_by' => $actor->id,
            ]);

            $reservation->save();
            $reservation->load(['room', 'user', 'user.division:id,name,code']);

            return [
                'success' => true,
                'data' => $reservation,
                'message' => ApiMessages::RESERVATION_UPDATED_SUCCESS,
            ];
        }, 3);
    }

    public function cancel(User $actor, Reservation $reservation): array
    {
        if (! $this->canAccess($actor, $reservation)) {
            return $this->forbidden();
        }

        if (in_array($reservation->status, [ReservationStatus::Rejected->value, ReservationStatus::Completed->value, ReservationStatus::Cancelled->value], true)) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::INVALID_RESERVATION_STATUS,
                'message' => ApiMessages::RESERVATION_CANCEL_INVALID_STATUS,
                'errors' => [],
            ];
        }

        if ($reservation->end_time->lte(now())) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::RESERVATION_ALREADY_FINISHED,
                'message' => ApiMessages::RESERVATION_ALREADY_FINISHED,
                'errors' => [],
            ];
        }

        $reservation->status = ReservationStatus::Cancelled->value;
        $reservation->updated_by = $actor->id;
        $reservation->save();
        $reservation->load(['room', 'user', 'user.division:id,name,code']);

        if ($reservation->user) {
            $reservation->user->notify(new ReservationCancelled($reservation));
        }

        return [
            'success' => true,
            'data' => $reservation,
            'message' => ApiMessages::RESERVATION_CANCELLED_SUCCESS,
        ];
    }

    /**
     * Mark a reservation as completed (manual endpoint or UI button).
     * Only allowed when the reservation has already ended.
     */
    public function complete(User $actor, Reservation $reservation): array
    {
        if (! $this->canAccess($actor, $reservation) && ! $actor->canApprove()) {
            // only owner or admin may complete
            return $this->forbidden();
        }

        if ($reservation->status !== ReservationStatus::Approved->value) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::INVALID_RESERVATION_STATUS,
                'message' => ApiMessages::RESERVATION_COMPLETE_INVALID_STATUS,
                'errors' => [],
            ];
        }

        if ($reservation->end_time->gt(now())) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::RESERVATION_NOT_FINISHED,
                'message' => ApiMessages::RESERVATION_NOT_FINISHED,
                'errors' => [],
            ];
        }

        $reservation->status = ReservationStatus::Completed->value;
        $reservation->updated_by = $actor->id;
        $reservation->save();
        $reservation->load(['room', 'user', 'user.division:id,name,code']);

        if ($reservation->user) {
            $reservation->user->notify(new ReservationCompleted($reservation));
        }

        return [
            'success' => true,
            'data' => $reservation,
            'message' => ApiMessages::RESERVATION_COMPLETED_SUCCESS,
        ];
    }

    /**
     * Automatic sweep run by scheduler/cron.
     *
     * @return array counts of modified rows ['expired' => int, 'completed' => int]
     */
    public function autoTransition(): array
    {
        $now = now();

        // Mark pending reservations as cancelled if they've started
        $expired = Reservation::query()
            ->where('status', ReservationStatus::Pending->value)
            ->where('start_time', '<=', $now)  // Changed from < to <= to handle exact time edge cases
            ->update(['status' => ReservationStatus::Cancelled->value, 'updated_by' => null]);

        // Mark approved reservations as completed if they've ended
        $completed = Reservation::query()
            ->where('status', ReservationStatus::Approved->value)
            ->where('end_time', '<=', $now)  // Changed from < to <= to handle exact time edge cases
            ->update(['status' => ReservationStatus::Completed->value, 'updated_by' => null]);

        return ['expired' => $expired, 'completed' => $completed];
    }

    public function approve(User $actor, Reservation $reservation, ?string $targetRoomId = null): array
    {
        if (! $actor->canApprove()) {
            return $this->forbidden();
        }

        if (! $reservation->isPending()) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::INVALID_RESERVATION_STATUS,
                'message' => ApiMessages::RESERVATION_APPROVE_PENDING_ONLY,
                'errors' => [],
            ];
        }

        // Use requested room if admin specified one, otherwise keep original
        $roomId = ($targetRoomId && $targetRoomId !== $reservation->room_id)
            ? $targetRoomId
            : $reservation->room_id;

        return DB::transaction(function () use ($actor, $reservation, $roomId) {
            // Lock the target room row to prevent concurrent approvals of conflicting reservations
            DB::table('m_rooms')->where('id', $roomId)->lockForUpdate()->first();

            // Re-read reservation inside transaction to get the latest status
            $reservation->refresh();

            if (! $reservation->isPending()) {
                return [
                    'success' => false,
                    'status_code' => 422,
                    'error_code' => ApiErrorCodes::INVALID_RESERVATION_STATUS,
                    'message' => ApiMessages::RESERVATION_APPROVE_PENDING_ONLY,
                    'errors' => [],
                ];
            }

            // When the room is being reassigned, don't exclude the old reservation ID
            // (it sits on a different room so can't conflict with itself anyway)
            $excludeId = ($roomId === $reservation->room_id) ? $reservation->id : null;

            $constraint = $this->cspService->validateReservation(
                $roomId,
                $reservation->start_time,
                $reservation->end_time,
                (int) $reservation->visitor_count,
                $excludeId
            );

            if (! $constraint['valid']) {
                return [
                    'success' => false,
                    'status_code' => 422,
                    'error_code' => ApiErrorCodes::RESERVATION_CONSTRAINT_FAILED,
                    'message' => ApiMessages::RESERVATION_CONSTRAINT_APPROVE_FAILED,
                    'errors' => ['constraints' => $constraint['errors']],
                ];
            }

            $reservation->room_id = $roomId;
            $reservation->status = ReservationStatus::Approved->value;
            $reservation->updated_by = $actor->id;
            $reservation->save();
            $reservation->load(['room', 'user', 'user.division:id,name,code']);

            if ($reservation->user) {
                $reservation->user->notify(new ReservationApproved($reservation));
            }

            return [
                'success' => true,
                'data' => $reservation,
                'message' => ApiMessages::RESERVATION_APPROVED_SUCCESS,
            ];
        }, 3);
    }

    public function reject(User $actor, Reservation $reservation): array
    {
        if (! $actor->canApprove()) {
            return $this->forbidden();
        }

        if (! $reservation->isPending()) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => ApiErrorCodes::INVALID_RESERVATION_STATUS,
                'message' => ApiMessages::RESERVATION_REJECT_PENDING_ONLY,
                'errors' => [],
            ];
        }

        $reservation->status = ReservationStatus::Rejected->value;
        $reservation->updated_by = $actor->id;
        $reservation->save();
        $reservation->load(['room', 'user', 'user.division:id,name,code']);

        if ($reservation->user) {
            $reservation->user->notify(new ReservationRejected($reservation));
        }

        return [
            'success' => true,
            'data' => $reservation,
            'message' => ApiMessages::RESERVATION_REJECTED_SUCCESS,
        ];
    }

    private function forbidden(): array
    {
        return [
            'success' => false,
            'status_code' => 403,
            'error_code' => ApiErrorCodes::FORBIDDEN,
            'message' => ApiMessages::FORBIDDEN,
            'errors' => [],
        ];
    }
}
