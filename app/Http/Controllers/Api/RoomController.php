<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Facility;
use App\Models\Room;
use App\Services\CSPService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'facility_ids' => $request->input('facility_ids'),
            'available_only' => $request->input('available_only'),
            'per_page' => $request->input('per_page'),
        ], [
            'location' => 'nullable|string|max:100',
            'min_capacity' => 'nullable|integer|min:1',
            'facility_ids' => 'nullable',
            'available_only' => 'nullable|in:true,false,1,0,True,False',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Room::query()
            ->with('facilities:id,name,slug')
            ->whereNull('deleted_at');

        if ($request->filled('location')) {
            $query->byLocation($request->input('location'));
        }

        if ($request->filled('min_capacity')) {
            $query->minCapacity((int) $request->input('min_capacity'));
        }

        $rawFacilityIds = $request->input('facility_ids');
        $facilityIds = is_array($rawFacilityIds)
            ? Facility::normalizeNames($rawFacilityIds)
            : Facility::parseInput(is_string($rawFacilityIds) ? $rawFacilityIds : null);

        if (!empty($facilityIds)) {
            $facilityValidator = Validator::make(
                ['facility_ids' => $facilityIds],
                ['facility_ids' => 'array', 'facility_ids.*' => 'string|max:50']
            );

            if ($facilityValidator->fails()) {
                return ApiResponse::validationError($facilityValidator->errors()->toArray());
            }

            $query->withFacilities($facilityIds);
        }

        // Apply explicit availability filter when parameter is provided:
        // true  => only available rooms (is_maintenance = false)
        // false => only unavailable rooms (is_maintenance = true)
        if ($request->has('available_only')) {
            $rawAvailableOnly = strtolower((string) $request->input('available_only'));
            $isAvailableOnly = in_array($rawAvailableOnly, ['true', '1'], true);

            if ($isAvailableOnly) {
                $query->available();
            } else {
                $query->where('is_maintenance', true);
            }
        }

        $query->orderBy('location')->orderBy('name');

        $perPage = $request->input('per_page');
        if ($perPage) {
            $paginated = $query->paginate((int) $perPage);
            return ApiResponse::paginated($paginated, ApiMessages::ROOM_LIST_SUCCESS);
        }

        $rooms = $query->get();

        return ApiResponse::success($rooms, ApiMessages::ROOM_LIST_SUCCESS, 200);
    }

    /**
     * Show room details by ID.
     */
    public function show(string $id): JsonResponse
    {
        $room = Room::query()
            ->with('facilities:id,name,slug')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$room) {
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        return ApiResponse::success($room, ApiMessages::ROOM_DETAIL_SUCCESS, 200);
    }

    /**
     * Create new room (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('m_rooms', 'name'),
            ],
            'location' => 'required|string|max:100',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1|max:1000',
            'facility_ids' => 'nullable|array',
            'facility_ids.*' => 'string|max:50',
            'is_maintenance' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        $room = new Room();
        $room->fill($validated);
        $room->is_maintenance = $request->boolean('is_maintenance', false);
        $room->created_by = $request->user()->id;
        $room->updated_by = $request->user()->id;
        $room->save();

        $facilityIds = Facility::resolveIds($validated['facility_ids'] ?? []);
        $room->facilities()->sync($facilityIds);
        $room->load('facilities:id,name,slug');

        return ApiResponse::success($room, ApiMessages::ROOM_CREATED_SUCCESS, 201);
    }

    /**
     * Update room (admin only).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $room = Room::query()->where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('m_rooms', 'name')->ignore($room->id),
            ],
            'location' => 'sometimes|required|string|max:100',
            'description' => 'sometimes|nullable|string',
            'capacity' => 'sometimes|required|integer|min:1|max:1000',
            'facility_ids' => 'sometimes|array',
            'facility_ids.*' => 'string|max:50',
            'is_maintenance' => 'sometimes|boolean',
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

        $room->fill($validated);

        if (array_key_exists('is_maintenance', $validated)) {
            $room->is_maintenance = (bool) $validated['is_maintenance'];
        }

        $room->updated_by = $request->user()->id;
        $room->save();

        if (array_key_exists('facility_ids', $validated)) {
            $room->facilities()->sync(Facility::resolveIds($validated['facility_ids']));
        }

        $room->load('facilities:id,name,slug');

        return ApiResponse::success($room, ApiMessages::ROOM_UPDATED_SUCCESS, 200);
    }

    /**
     * Delete room (admin only).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $room = Room::query()->where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        $room->deleted_by = $request->user()->id;
        $room->save();
        $room->delete();

        return ApiResponse::success(null, ApiMessages::ROOM_DELETED_SUCCESS, 200);
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
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        if ($room->is_maintenance) {
            return ApiResponse::error(
                ApiErrorCodes::ROOM_UNDER_MAINTENANCE,
                ApiMessages::ROOM_UNDER_MAINTENANCE,
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
        ], ApiMessages::ROOM_AVAILABILITY_SUCCESS, 200);
    }

    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return ApiResponse::forbidden();
        }

        return null;
    }
}
