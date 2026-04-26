<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Reservation;
use App\Models\RoomComplaint;
use App\Services\ImageService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ComplaintController extends Controller
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * List complaints.
     *
     * GET /v1/complaints
     *
     * Admin sees all complaints. Regular users see only their own.
     * Filters: status, room_id, reservation_id, per_page.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status'         => 'nullable|in:open,in_progress,resolved,rejected',
            'room_id'        => 'nullable|string|exists:m_rooms,id',
            'reservation_id' => 'nullable|string|exists:t_reservations,id',
            'per_page'       => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();

        $query = RoomComplaint::query()
            ->with(['reservation', 'room', 'facility', 'reporter'])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if (!$user->canApprove()) {
            $query->forReporter($user->id);
        }

        if ($request->filled('status')) {
            $query->status($request->input('status'));
        }

        if ($request->filled('room_id')) {
            $query->forRoom($request->input('room_id'));
        }

        if ($request->filled('reservation_id')) {
            $query->where('reservation_id', $request->input('reservation_id'));
        }

        $perPage = $request->input('per_page');
        if ($perPage) {
            return ApiResponse::paginated(
                $query->paginate((int) $perPage),
                ApiMessages::COMPLAINT_LIST_SUCCESS
            );
        }

        return ApiResponse::success($query->get(), ApiMessages::COMPLAINT_LIST_SUCCESS);
    }

    /**
     * Get a single complaint.
     *
     * GET /v1/complaints/{id}
     *
     * Users can only view their own complaints; admins can view any.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $complaint = RoomComplaint::query()
            ->with(['reservation', 'room', 'facility', 'reporter', 'resolver'])
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$complaint) {
            return ApiResponse::notFound(ApiMessages::COMPLAINT_NOT_FOUND);
        }

        $user = $request->user();
        if (!$user->canApprove() && $complaint->reported_by !== $user->id) {
            return ApiResponse::forbidden();
        }

        return ApiResponse::success($complaint, ApiMessages::COMPLAINT_DETAIL_SUCCESS);
    }

    /**
     * Submit a new complaint.
     *
     * POST /v1/complaints
     *
     * Accepts multipart/form-data. Photo is optional (max 10 MB, auto-compressed above 2 MB).
     * The referenced reservation must be in status "completed".
     * Users may only reference their own reservations; admins may reference any.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|string|exists:t_reservations,id',
            'facility_id'    => 'nullable|string|exists:m_facilities,id',
            'title'          => 'required|string|max:200',
            'description'    => 'required|string|max:2000',
            'photo'          => ImageService::validationRules(),
        ], array_merge([
            'reservation_id.required' => 'Reservasi wajib dipilih.',
            'reservation_id.exists'   => 'Reservasi tidak ditemukan.',
            'facility_id.exists'      => 'Fasilitas tidak ditemukan.',
            'title.required'          => 'Judul komplain wajib diisi.',
            'title.max'               => 'Judul komplain maksimal 200 karakter.',
            'description.required'    => 'Deskripsi komplain wajib diisi.',
            'description.max'         => 'Deskripsi maksimal 2.000 karakter.',
        ], ImageService::validationMessages('photo')));

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $reservationId = $request->input('reservation_id');

        $reservation = Reservation::query()
            ->where('id', $reservationId)
            ->whereNull('deleted_at')
            ->first();

        if (!$reservation) {
            return ApiResponse::notFound(ApiMessages::RESERVATION_NOT_FOUND);
        }

        // Non-admin users may only complain about their own reservations
        if (!$user->canApprove() && $reservation->user_id !== $user->id) {
            return ApiResponse::forbidden();
        }

        // Complaints are only allowed for completed reservations
        if ($reservation->status !== 'completed') {
            return ApiResponse::error(
                ApiErrorCodes::COMPLAINT_INVALID_RESERVATION_STATUS,
                ApiMessages::COMPLAINT_INVALID_RESERVATION_STATUS,
                422
            );
        }

        // Handle optional photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            try {
                $result = $this->imageService->upload($request->file('photo'), 'complaints');
                $photoPath = $result['path'];
            } catch (Throwable) {
                return ApiResponse::error(
                    ApiErrorCodes::IMAGE_UPLOAD_FAILED,
                    ApiMessages::IMAGE_UPLOAD_FAILED,
                    500
                );
            }
        }

        $complaint = RoomComplaint::create([
            'reservation_id' => $reservationId,
            'reported_by'    => $user->id,
            'room_id'        => $reservation->room_id,
            'facility_id'    => $request->input('facility_id'),
            'title'          => $request->input('title'),
            'description'    => $request->input('description'),
            'photo_path'     => $photoPath,
            'status'         => 'open',
            'created_by'     => $user->id,
            'updated_by'     => $user->id,
        ]);

        $complaint->load(['reservation', 'room', 'facility', 'reporter']);

        return ApiResponse::success($complaint, ApiMessages::COMPLAINT_CREATED_SUCCESS, 201);
    }

    /**
     * Update the status of a complaint.
     *
     * PATCH /v1/complaints/{id}/status
     *
     * Admin only. Valid transitions:
     *   open         → in_progress | resolved | rejected
     *   in_progress  → resolved | rejected
     * Once resolved or rejected the complaint is closed and cannot be changed.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        if (!$request->user()?->canApprove()) {
            return ApiResponse::error(ApiErrorCodes::FORBIDDEN, ApiMessages::FORBIDDEN, 403);
        }

        $complaint = RoomComplaint::query()
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$complaint) {
            return ApiResponse::notFound(ApiMessages::COMPLAINT_NOT_FOUND);
        }

        if ($complaint->isClosed()) {
            return ApiResponse::error(
                ApiErrorCodes::COMPLAINT_ALREADY_CLOSED,
                ApiMessages::COMPLAINT_ALREADY_CLOSED,
                422
            );
        }

        $validator = Validator::make($request->all(), [
            'status'           => 'required|in:in_progress,resolved,rejected',
            'resolution_notes' => 'nullable|string|max:2000',
        ], [
            'status.required' => 'Status wajib diisi.',
            'status.in'       => 'Status tidak valid. Pilihan: in_progress, resolved, rejected.',
            'resolution_notes.max' => 'Catatan resolusi maksimal 2.000 karakter.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $newStatus = $request->input('status');

        $updates = [
            'status'           => $newStatus,
            'resolution_notes' => $request->input('resolution_notes'),
            'updated_by'       => $request->user()->id,
        ];

        if (in_array($newStatus, ['resolved', 'rejected'])) {
            $updates['resolved_at'] = Carbon::now()->utc();
            $updates['resolved_by'] = $request->user()->id;
        }

        $complaint->update($updates);
        $complaint->load(['reservation', 'room', 'facility', 'reporter', 'resolver']);

        $message = match ($newStatus) {
            'in_progress' => ApiMessages::COMPLAINT_STATUS_IN_PROGRESS,
            'resolved'    => ApiMessages::COMPLAINT_STATUS_RESOLVED,
            'rejected'    => ApiMessages::COMPLAINT_STATUS_REJECTED,
            default       => ApiMessages::SUCCESS_GENERIC,
        };

        return ApiResponse::success($complaint, $message);
    }
}
