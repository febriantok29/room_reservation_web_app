<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,approved,rejected,completed,cancelled',
            'room_id' => 'nullable|string|exists:m_rooms,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = $this->reservationService->queryFor($request->user())
            ->orderBy('start_time', 'desc');

        if ($request->filled('status')) {
            $query->status($request->input('status'));
        }

        if ($request->filled('room_id')) {
            $query->forRoom($request->input('room_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('start_time', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('start_time', '<=', $request->input('date_to'));
        }

        $perPage = $request->input('per_page');
        if ($perPage) {
            $paginated = $query->paginate((int) $perPage);

            return ApiResponse::paginated($paginated, ApiMessages::RESERVATION_LIST_SUCCESS);
        }

        return ApiResponse::success($query->get(), ApiMessages::RESERVATION_LIST_SUCCESS);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::query()
            ->with(['room', 'user'])
            ->where('id', $id)
            ->first();

        if (!$reservation) {
            return ApiResponse::notFound(ApiMessages::RESERVATION_NOT_FOUND);
        }

        if (!$this->reservationService->canAccess($request->user(), $reservation)) {
            return ApiResponse::forbidden();
        }

        return ApiResponse::success($reservation, ApiMessages::RESERVATION_DETAIL_SUCCESS);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|string|exists:m_rooms,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'nullable|string|max:1000',
            'visitor_count' => 'required|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $result = $this->reservationService->create($request->user(), $validator->validated());

        if (!$result['success']) {
            return ApiResponse::error(
                $result['error_code'],
                $result['message'],
                $result['status_code'],
                $result['errors']
            );
        }

        return ApiResponse::success($result['data'], $result['message'], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::query()->where('id', $id)->first();

        if (!$reservation) {
            return ApiResponse::notFound(ApiMessages::RESERVATION_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'room_id' => 'sometimes|required|string|exists:m_rooms,id',
            'start_time' => 'sometimes|required|date|after:now',
            'end_time' => 'sometimes|required|date|after:start_time',
            'purpose' => 'sometimes|nullable|string|max:1000',
            'visitor_count' => 'sometimes|required|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();
        if (empty($validated)) {
            return ApiResponse::error(
                ApiErrorCodes::NO_UPDATE_PAYLOAD,
                ApiMessages::NO_UPDATE_PAYLOAD,
                422
            );
        }

        if (!isset($validated['end_time']) && isset($validated['start_time'])) {
            $validated['end_time'] = $reservation->end_time;
        }

        if (isset($validated['end_time']) && !isset($validated['start_time'])) {
            $validated['start_time'] = $reservation->start_time;
        }

        if (isset($validated['start_time'], $validated['end_time'])
            && strtotime((string) $validated['start_time']) >= strtotime((string) $validated['end_time'])) {
            return ApiResponse::validationError([
                'end_time' => [ApiMessages::RESERVATION_END_TIME_AFTER_START],
            ]);
        }

        $result = $this->reservationService->update($request->user(), $reservation, $validated);

        if (!$result['success']) {
            return ApiResponse::error(
                $result['error_code'],
                $result['message'],
                $result['status_code'],
                $result['errors']
            );
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::query()->where('id', $id)->first();

        if (!$reservation) {
            return ApiResponse::notFound(ApiMessages::RESERVATION_NOT_FOUND);
        }

        $result = $this->reservationService->cancel($request->user(), $reservation);

        if (!$result['success']) {
            return ApiResponse::error(
                $result['error_code'],
                $result['message'],
                $result['status_code'],
                $result['errors']
            );
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::query()->where('id', $id)->first();

        if (!$reservation) {
            return ApiResponse::notFound(ApiMessages::RESERVATION_NOT_FOUND);
        }

        $result = $this->reservationService->approve($request->user(), $reservation);

        if (!$result['success']) {
            return ApiResponse::error(
                $result['error_code'],
                $result['message'],
                $result['status_code'],
                $result['errors']
            );
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::query()->where('id', $id)->first();

        if (!$reservation) {
            return ApiResponse::notFound(ApiMessages::RESERVATION_NOT_FOUND);
        }

        $result = $this->reservationService->reject($request->user(), $reservation);

        if (!$result['success']) {
            return ApiResponse::error(
                $result['error_code'],
                $result['message'],
                $result['status_code'],
                $result['errors']
            );
        }

        return ApiResponse::success($result['data'], $result['message']);
    }
}
