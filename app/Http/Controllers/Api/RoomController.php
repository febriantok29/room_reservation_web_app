<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Facility;
use App\Models\Room;
use App\Services\CSPService;
use App\Services\ImageService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Throwable;

class RoomController extends Controller
{
    private CSPService $cspService;
    private ImageService $imageService;

    public function __construct(CSPService $cspService, ImageService $imageService)
    {
        $this->cspService = $cspService;
        $this->imageService = $imageService;
    }

    /**
     * List rooms with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make([
            'floor' => $request->input('floor'),
            'min_capacity' => $request->input('min_capacity'),
            'facility_ids' => $request->input('facility_ids'),
            'available_only' => $request->input('available_only'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'per_page' => $request->input('per_page'),
            'search'    => $request->input('search'),
        ], [
            'floor' => 'nullable|integer|min:1|max:99',
            'min_capacity' => 'nullable|integer|min:1',
            'facility_ids' => 'nullable',
            'available_only' => 'nullable|in:true,false,1,0,True,False',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|required_with:start_time|date|after:start_time',
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Room::query()
            ->with('facilities:id,name,slug')
            ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $keyword = trim((string) $request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('floor')) {
            $query->byFloor((int) $request->input('floor'));
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

        // Time-range availability filters. When both start_time and end_time are
        // provided we exclude rooms that have an overlapping reservation in the
        // given interval. The logic mirrors CSPService::isRoomAvailable().
        if ($request->filled('start_time') && $request->filled('end_time')) {
            $start = Carbon::parse($request->input('start_time'));
            $end = Carbon::parse($request->input('end_time'));

            $query->whereDoesntHave('reservations', function ($q) use ($start, $end) {
                $q->whereNull('deleted_at')
                  ->whereIn('status', ['pending', 'approved'])
                  ->where(function ($sub) use ($start, $end) {
                      $sub->where(function ($a) use ($start, $end) {
                          $a->where('start_time', '<=', $start)
                            ->where('end_time', '>', $start);
                      })
                      ->orWhere(function ($a) use ($start, $end) {
                          $a->where('start_time', '<', $end)
                            ->where('end_time', '>=', $end);
                      })
                      ->orWhere(function ($a) use ($start, $end) {
                          $a->where('start_time', '>=', $start)
                            ->where('end_time', '<=', $end);
                      })
                      ->orWhere(function ($a) use ($start, $end) {
                          $a->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                      });
                  });
            });
        }

        $query->orderBy('floor')->orderBy('name');

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
            'floor' => 'required|integer|min:1|max:99',
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
            'floor' => 'sometimes|required|integer|min:1|max:99',
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
     * List all rooms available for a specific time window.
     *
     * GET /v1/rooms/available?start_time=...&end_time=...&visitor_count=1
     */
    public function available(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_time'    => 'required|date',
            'end_time'      => 'required|date|after:start_time',
            'visitor_count' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $start        = Carbon::parse($request->input('start_time'))->utc();
        $end          = Carbon::parse($request->input('end_time'))->utc();
        $visitorCount = (int) $request->input('visitor_count', 1);

        $rooms = Room::query()
            ->with('facilities')
            ->whereNull('deleted_at')
            ->where('is_maintenance', false)
            ->where('capacity', '>=', $visitorCount)
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        // Filter out rooms that have an overlapping approved/pending reservation
        $available = $rooms->filter(function (Room $room) use ($start, $end) {
            return $this->cspService->isRoomAvailable($room->id, $start, $end);
        })->values();

        return ApiResponse::success([
            'start_time'    => $start->toIso8601String(),
            'end_time'      => $end->toIso8601String(),
            'visitor_count' => $visitorCount,
            'total'         => $available->count(),
            'rooms'         => $available,
        ], ApiMessages::ROOM_AVAILABLE_LIST_SUCCESS);
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

    /**
     * Upload or replace the image for a room.
     *
     * POST /v1/rooms/{id}/image
     *
     * Accepts multipart/form-data with field "image".
     * Files larger than 2 MB are auto-compressed to JPEG.
     * Server accepts up to 10 MB as incoming file size.
     */
    public function storeImage(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $room = Room::query()->where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        $validator = Validator::make(
            $request->all(),
            ['image' => ImageService::validationRules(required: true)],
            ImageService::validationMessages('image')
        );

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        try {
            $result = $this->imageService->upload(
                $request->file('image'),
                'rooms',
                $room->image_path
            );
        } catch (Throwable) {
            return ApiResponse::error(
                ApiErrorCodes::IMAGE_UPLOAD_FAILED,
                ApiMessages::IMAGE_UPLOAD_FAILED,
                500
            );
        }

        $room->image_path = $result['path'];
        $room->updated_by = $request->user()->id;
        $room->save();

        return ApiResponse::success([
            'image_id'       => $result['image_id'],
            'image_url'      => $result['url'],
            'size_bytes'     => $result['size_bytes'],
            'was_compressed' => $result['was_compressed'],
        ], ApiMessages::ROOM_IMAGE_UPLOADED_SUCCESS, 200);
    }

    /**
     * Delete the image for a room.
     *
     * DELETE /v1/rooms/{id}/image
     */
    public function destroyImage(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $room = Room::query()->where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        if (!$room->image_path) {
            return ApiResponse::error(
                ApiErrorCodes::IMAGE_NOT_FOUND,
                ApiMessages::ROOM_IMAGE_NOT_FOUND,
                404
            );
        }

        $this->imageService->delete($room->image_path);
        $room->image_path = null;
        $room->updated_by = $request->user()->id;
        $room->save();

        return ApiResponse::success(null, ApiMessages::ROOM_IMAGE_DELETED_SUCCESS, 200);
    }

    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return ApiResponse::forbidden();
        }

        return null;
    }
}
