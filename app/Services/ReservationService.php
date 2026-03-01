<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReservationService
{
    private CSPService $cspService;

    public function __construct(CSPService $cspService)
    {
        $this->cspService = $cspService;
    }

    public function queryFor(User $actor): Builder
    {
        $query = Reservation::query()->with(['room', 'user']);

        if (!$actor->canApprove()) {
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
        $constraint = $this->cspService->validateReservation(
            $payload['room_id'],
            $payload['start_time'],
            $payload['end_time'],
            (int) $payload['visitor_count']
        );

        if (!$constraint['valid']) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'RESERVATION_CONSTRAINT_FAILED',
                'message' => 'Reservasi tidak memenuhi aturan penjadwalan',
                'errors' => ['constraints' => $constraint['errors']],
            ];
        }

        $reservation = Reservation::create([
            'user_id' => $actor->id,
            'room_id' => $payload['room_id'],
            'start_time' => Carbon::parse($payload['start_time']),
            'end_time' => Carbon::parse($payload['end_time']),
            'purpose' => $payload['purpose'] ?? null,
            'visitor_count' => (int) $payload['visitor_count'],
            'status' => 'pending',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $reservation->load(['room', 'user']);

        return [
            'success' => true,
            'data' => $reservation,
            'message' => 'Reservasi berhasil dibuat dan menunggu persetujuan',
        ];
    }

    public function update(User $actor, Reservation $reservation, array $payload): array
    {
        if (!$this->canAccess($actor, $reservation)) {
            return [
                'success' => false,
                'status_code' => 403,
                'error_code' => 'FORBIDDEN',
                'message' => 'Anda tidak memiliki akses ke resource ini',
                'errors' => [],
            ];
        }

        if (!$reservation->isPending()) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'INVALID_RESERVATION_STATUS',
                'message' => 'Hanya reservasi dengan status pending yang dapat diubah',
                'errors' => [],
            ];
        }

        if ($reservation->start_time->lte(now())) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'RESERVATION_ALREADY_STARTED',
                'message' => 'Reservasi yang sudah dimulai tidak dapat diubah',
                'errors' => [],
            ];
        }

        $roomId = $payload['room_id'] ?? $reservation->room_id;
        $startTime = $payload['start_time'] ?? $reservation->start_time;
        $endTime = $payload['end_time'] ?? $reservation->end_time;
        $visitorCount = (int) ($payload['visitor_count'] ?? $reservation->visitor_count);

        $constraint = $this->cspService->validateReservation(
            $roomId,
            $startTime,
            $endTime,
            $visitorCount,
            $reservation->id
        );

        if (!$constraint['valid']) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'RESERVATION_CONSTRAINT_FAILED',
                'message' => 'Perubahan reservasi tidak memenuhi aturan penjadwalan',
                'errors' => ['constraints' => $constraint['errors']],
            ];
        }

        $reservation->fill([
            'room_id' => $roomId,
            'start_time' => Carbon::parse($startTime),
            'end_time' => Carbon::parse($endTime),
            'purpose' => $payload['purpose'] ?? $reservation->purpose,
            'visitor_count' => $visitorCount,
            'updated_by' => $actor->id,
        ]);

        $reservation->save();
        $reservation->load(['room', 'user']);

        return [
            'success' => true,
            'data' => $reservation,
            'message' => 'Reservasi berhasil diperbarui',
        ];
    }

    public function cancel(User $actor, Reservation $reservation): array
    {
        if (!$this->canAccess($actor, $reservation)) {
            return [
                'success' => false,
                'status_code' => 403,
                'error_code' => 'FORBIDDEN',
                'message' => 'Anda tidak memiliki akses ke resource ini',
                'errors' => [],
            ];
        }

        if (in_array($reservation->status, ['rejected', 'completed', 'cancelled'], true)) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'INVALID_RESERVATION_STATUS',
                'message' => 'Status reservasi saat ini tidak dapat dibatalkan',
                'errors' => [],
            ];
        }

        if ($reservation->end_time->lte(now())) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'RESERVATION_ALREADY_FINISHED',
                'message' => 'Reservasi yang sudah selesai tidak dapat dibatalkan',
                'errors' => [],
            ];
        }

        $reservation->status = 'cancelled';
        $reservation->updated_by = $actor->id;
        $reservation->save();
        $reservation->load(['room', 'user']);

        return [
            'success' => true,
            'data' => $reservation,
            'message' => 'Reservasi berhasil dibatalkan',
        ];
    }

    public function approve(User $actor, Reservation $reservation): array
    {
        if (!$actor->canApprove()) {
            return [
                'success' => false,
                'status_code' => 403,
                'error_code' => 'FORBIDDEN',
                'message' => 'Anda tidak memiliki akses ke resource ini',
                'errors' => [],
            ];
        }

        if (!$reservation->isPending()) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'INVALID_RESERVATION_STATUS',
                'message' => 'Hanya reservasi dengan status pending yang dapat disetujui',
                'errors' => [],
            ];
        }

        $constraint = $this->cspService->validateReservation(
            $reservation->room_id,
            $reservation->start_time,
            $reservation->end_time,
            (int) $reservation->visitor_count,
            $reservation->id
        );

        if (!$constraint['valid']) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'RESERVATION_CONSTRAINT_FAILED',
                'message' => 'Reservasi tidak dapat disetujui karena melanggar aturan',
                'errors' => ['constraints' => $constraint['errors']],
            ];
        }

        $reservation->status = 'approved';
        $reservation->updated_by = $actor->id;
        $reservation->save();
        $reservation->load(['room', 'user']);

        return [
            'success' => true,
            'data' => $reservation,
            'message' => 'Reservasi berhasil disetujui',
        ];
    }

    public function reject(User $actor, Reservation $reservation): array
    {
        if (!$actor->canApprove()) {
            return [
                'success' => false,
                'status_code' => 403,
                'error_code' => 'FORBIDDEN',
                'message' => 'Anda tidak memiliki akses ke resource ini',
                'errors' => [],
            ];
        }

        if (!$reservation->isPending()) {
            return [
                'success' => false,
                'status_code' => 422,
                'error_code' => 'INVALID_RESERVATION_STATUS',
                'message' => 'Hanya reservasi dengan status pending yang dapat ditolak',
                'errors' => [],
            ];
        }

        $reservation->status = 'rejected';
        $reservation->updated_by = $actor->id;
        $reservation->save();
        $reservation->load(['room', 'user']);

        return [
            'success' => true,
            'data' => $reservation,
            'message' => 'Reservasi berhasil ditolak',
        ];
    }
}
