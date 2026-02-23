<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Room;
use App\Services\CSPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    private CSPService $cspService;

    public function __construct(CSPService $cspService)
    {
        $this->cspService = $cspService;
    }

    /**
     * List rooms with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make([
            'location' => $request->input('location'),
            'min_capacity' => $request->input('min_capacity'),
            'available_only' => $request->input('available_only'),
            'per_page' => $request->input('per_page'),
        ], [
            'location' => 'nullable|string|max:100',
            'min_capacity' => 'nullable|integer|min:1',
            'available_only' => 'nullable|in:true,false,1,0,True,False',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Room::query()->whereNull('deleted_at');

        if ($request->filled('location')) {
            $query->byLocation($request->input('location'));
        }

        if ($request->filled('min_capacity')) {
            $query->minCapacity((int) $request->input('min_capacity'));
        }

        // Use request()->boolean() which handles both query and form data
        if ($request->boolean('available_only', false)) {
            $query->available();
        }

        $query->orderBy('location')->orderBy('name');

        $perPage = $request->input('per_page');
        if ($perPage) {
            $paginated = $query->paginate((int) $perPage);
            return ApiResponse::paginated($paginated, 'Data ruangan berhasil diambil');
        }

        $rooms = $query->get();

        return ApiResponse::success($rooms, 'Data ruangan berhasil diambil', 200);
    }

    /**
     * Show room details by ID.
     */
    public function show(string $id): JsonResponse
    {
        $room = Room::where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound('Ruangan tidak ditemukan');
        }

        return ApiResponse::success($room, 'Detail ruangan berhasil diambil', 200);
    }

    /**
     * Get available time slots for a room.
     */
    public function availability(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'interval_minutes' => 'nullable|integer|min:15|max:240',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $room = Room::where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound('Ruangan tidak ditemukan');
        }

        if ($room->is_maintenance) {
            return ApiResponse::error(
                'ROOM_UNDER_MAINTENANCE',
                'Ruangan sedang dalam perawatan',
                400
            );
        }

        $intervalMinutes = (int) $request->input('interval_minutes', 30);
        $slots = $this->cspService->getAvailableTimeSlots($id, $request->input('date'), $intervalMinutes);

        return ApiResponse::success([
            'room_id' => $room->id,
            'date' => $request->input('date'),
            'interval_minutes' => $intervalMinutes,
            'available_slots' => $slots,
        ], 'Slot tersedia berhasil diambil', 200);
    }
}
