<?php

namespace App\Http\Controllers\Web;

use App\Helpers\TimezoneHelper;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomComplaint;
use App\Models\User;
use App\Services\ImageService;
use App\Services\ReservationService;
use App\Support\WebMessages;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AdminDashboardController extends Controller
{
    private ReservationService $reservationService;
    private ImageService $imageService;

    public function __construct(ReservationService $reservationService, ImageService $imageService)
    {
        $this->reservationService = $reservationService;
        $this->imageService = $imageService;
    }

    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function dashboard(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $today = Carbon::today();
        $allRooms = Room::query()->whereNull('deleted_at')->get(['id', 'name', 'floor', 'is_maintenance']);

        $summary = [
            'total_rooms'           => $allRooms->count(),
            'maintenance_rooms'     => $allRooms->where('is_maintenance', true)->count(),
            'total_users'           => User::query()->whereNull('deleted_at')->count(),
            'pending_reservations'  => Reservation::query()->pending()->count(),
            'approved_reservations' => Reservation::query()->approved()->count(),
            'today_reservations'    => Reservation::query()
                ->whereDate('start_time', $today)
                ->whereIn('status', ['approved', 'pending'])
                ->count(),
            'open_complaints'       => RoomComplaint::query()
                ->whereNull('deleted_at')
                ->where('status', 'open')
                ->count(),
            'in_progress_complaints' => RoomComplaint::query()
                ->whereNull('deleted_at')
                ->where('status', 'in_progress')
                ->count(),
        ];

        $latestReservations = Reservation::query()
            ->with(['room', 'user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Room status grid: show maintenance state + whether booked today
        $bookedRoomIds = Reservation::query()
            ->whereDate('start_time', $today)
            ->whereIn('status', ['approved', 'pending'])
            ->pluck('room_id')
            ->unique();

        $roomStatuses = $allRooms->map(function ($room) use ($bookedRoomIds) {
            return [
                'id'           => $room->id,
                'name'         => $room->name,
                'floor'        => $room->floor,
                'maintenance'  => (bool) $room->is_maintenance,
                'booked_today' => $bookedRoomIds->contains($room->id),
            ];
        })->sortBy('floor')->values();

        return view('admin.dashboard', [
            'summary'            => $summary,
            'latestReservations' => $latestReservations,
            'roomStatuses'       => $roomStatuses,
        ]);
    }

    public function rooms(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $searchQuery = trim((string) $request->query('q', ''));
        $maintenanceFilter = trim((string) $request->query('maintenance', ''));

        $roomsQuery = Room::query()
            ->with('facilities:id,name,slug')
            ->whereNull('deleted_at');

        if ($searchQuery !== '') {
            $roomsQuery->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%")
                        ->orWhereRaw('CAST(floor AS CHAR) LIKE ?', ["%{$searchQuery}%"])
                    ->orWhere('description', 'like', "%{$searchQuery}%")
                    ->orWhereHas('facilities', function ($facilityQuery) use ($searchQuery) {
                        $facilityQuery->where('name', 'like', "%{$searchQuery}%")
                            ->orWhere('slug', 'like', "%{$searchQuery}%");
                    });
            });
        }

        if (in_array($maintenanceFilter, ['0', '1'], true)) {
            $roomsQuery->where('is_maintenance', $maintenanceFilter === '1');
        }

        $rooms = $roomsQuery
                ->orderBy('floor')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.rooms.index', [
            'rooms' => $rooms,
            'searchQuery' => $searchQuery,
            'maintenanceFilter' => $maintenanceFilter,
        ]);
    }

    public function createRoom(Request $request): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.rooms.create', [
            'allFacilities' => Facility::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
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
            'facility_ids.*' => 'nullable|string|exists:m_facilities,id',
            'is_maintenance' => 'nullable|boolean',
            'image' => ImageService::validationRules(),
        ]);

        $room = new Room();
        $room->fill([
            'name' => $validated['name'],
            'floor' => (int) $validated['floor'],
            'description' => $validated['description'] ?? null,
            'capacity' => $validated['capacity'],
        ]);
        $room->is_maintenance = $request->boolean('is_maintenance', false);
        $room->created_by = $request->user()->id;
        $room->updated_by = $request->user()->id;
        $room->save();

        if ($request->hasFile('image')) {
            try {
                $result = $this->imageService->upload($request->file('image'), 'rooms');
                $room->image_path = $result['path'];
                $room->save();
            } catch (Throwable) {
                // Image upload failed, but room was created
                // Continue without image - admin can add later
            }
        }

        $facilityIds = array_filter($validated['facility_ids'] ?? []);
        $room->facilities()->sync($facilityIds);

        return redirect()
            ->route('admin.rooms')
            ->with('success', WebMessages::ROOM_CREATED_SUCCESS);
    }

    public function editRoom(Request $request, Room $room): View
    {
        $this->ensureAdminAccess($request);

        $room->load('facilities:id,name,slug');

        return view('admin.rooms.edit', [
            'room' => $room,
            'allFacilities' => Facility::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function updateRoom(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('m_rooms', 'name')->ignore($room->id),
            ],
            'floor' => 'required|integer|min:1|max:99',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1|max:1000',
            'facility_ids' => 'nullable|array',
            'facility_ids.*' => 'nullable|string|exists:m_facilities,id',
            'is_maintenance' => 'nullable|boolean',
            'image' => ImageService::validationRules(),
        ]);

        $room->fill([
            'name' => $validated['name'],
            'floor' => (int) $validated['floor'],
            'description' => $validated['description'] ?? null,
            'capacity' => $validated['capacity'],
        ]);
        $room->is_maintenance = $request->boolean('is_maintenance', false);
        $room->updated_by = $request->user()->id;

        if ($request->hasFile('image')) {
            try {
                $result = $this->imageService->upload(
                    $request->file('image'),
                    'rooms',
                    $room->image_path
                );
                $room->image_path = $result['path'];
            } catch (Throwable) {
                // Image upload failed, continue with other updates
            }
        }

        $room->save();

        $facilityIds = array_filter($validated['facility_ids'] ?? []);
        $room->facilities()->sync($facilityIds);

        return redirect()
            ->route('admin.rooms')
            ->with('success', WebMessages::ROOM_UPDATED_SUCCESS);
    }

    public function destroyRoom(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $room->deleted_by = $request->user()->id;
        $room->save();
        $room->delete();

        return redirect()
            ->route('admin.rooms')
            ->with('success', WebMessages::ROOM_DELETED_SUCCESS);
    }

    public function destroyRoomImage(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        if ($room->image_path) {
            $this->imageService->delete($room->image_path);
            $room->image_path = null;
            $room->updated_by = $request->user()->id;
            $room->save();
        }

        return redirect()
            ->route('admin.rooms.edit', $room->id)
            ->with('success', WebMessages::ROOM_IMAGE_DELETED_SUCCESS);
    }

    public function reservations(Request $request): View
    {
        $this->ensureAdminAccess($request);
        // backstop: run automatic transition whenever admin views list
        // This ensures approved reservations that have passed their end_time become 'completed'
        // and pending reservations that have passed their start_time become 'cancelled'
        $this->reservationService->autoTransition();

        $searchQuery = trim((string) $request->query('q', ''));
        $statusFilter = strtolower(trim((string) $request->query('status', '')));
        $allowedStatuses = ['pending', 'approved', 'rejected', 'completed', 'cancelled'];
        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = '';
        }

        $reservationsQuery = Reservation::query()
            ->with(['room', 'user']);

        if ($searchQuery !== '') {
            $reservationsQuery->where(function ($query) use ($searchQuery) {
                $query->where('id', 'like', "%{$searchQuery}%")
                    ->orWhere('purpose', 'like', "%{$searchQuery}%")
                    ->orWhereHas('room', function ($roomQuery) use ($searchQuery) {
                        $roomQuery->where('name', 'like', "%{$searchQuery}%")
                                ->orWhereRaw('CAST(floor AS CHAR) LIKE ?', ["%{$searchQuery}%"]);
                    })
                    ->orWhereHas('user', function ($userQuery) use ($searchQuery) {
                        $userQuery->where('first_name', 'like', "%{$searchQuery}%")
                            ->orWhere('last_name', 'like', "%{$searchQuery}%")
                            ->orWhereRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ["%{$searchQuery}%"])
                            ->orWhere('employee_id', 'like', "%{$searchQuery}%")
                            ->orWhere('email', 'like', "%{$searchQuery}%");
                    });
            });
        }

        if ($statusFilter !== '') {
            $reservationsQuery->where('status', $statusFilter);
        }

        $reservations = $reservationsQuery
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        return view('admin.reservations.index', [
            'reservations' => $reservations,
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function createReservation(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $rooms = Room::query()
            ->with('facilities:id,name,slug')
            ->whereNull('deleted_at')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        $allFacilities = Facility::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $users = User::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $debugUser = $users->isNotEmpty()
            ? $users->random()
            : null;

        return view('admin.reservations.create', [
            'rooms' => $rooms,
            'users' => $users,
            'allFacilities' => $allFacilities,
            'debugUser' => $debugUser,
        ]);
    }

    public function storeReservation(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate(
            [
                'user_id' => 'nullable|string|exists:s_users,id',
                'room_id' => 'required|string|exists:m_rooms,id',
                'reservation_date' => 'required|date',
                'start_clock' => 'required|date_format:H:i',
                'end_clock' => 'required|date_format:H:i',
                'purpose' => 'nullable|string|max:1000',
                'visitor_count' => 'required|integer|min:1|max:1000',
            ],
            $this->reservationValidationMessages(),
            $this->reservationValidationAttributes()
        );

        [$startTime, $endTime] = $this->buildReservationDateTimes($validated['reservation_date'], $validated['start_clock'], $validated['end_clock']);

        if ($startTime->gte($endTime)) {
            return back()->withErrors(['end_clock' => WebMessages::RESERVATION_END_AFTER_START])->withInput();
        }

        if ($startTime->lte(now())) {
            return back()->withErrors(['start_clock' => WebMessages::RESERVATION_START_AFTER_NOW])->withInput();
        }

        try {
            $result = $this->reservationService->create($request->user(), [
                'user_id' => $validated['user_id'] ?? $request->user()->id,
                'room_id' => $validated['room_id'],
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'purpose' => $validated['purpose'] ?? null,
                'visitor_count' => (int) $validated['visitor_count'],
            ]);

            if (!$result['success']) {
                return back()->withErrors($this->formatReservationServiceErrors($result))->withInput();
            }

            return redirect()
                ->route('admin.reservations')
                ->with('success', WebMessages::RESERVATION_CREATED_SUCCESS);
        } catch (Throwable $exception) {
            return back()->withErrors(['reservation' => WebMessages::RESERVATION_STORE_FAILED])->withInput();
        }
    }

    public function editReservation(Request $request, Reservation $reservation): View
    {
        $this->ensureAdminAccess($request);

        // Ensure status is current in case scheduler hasn't run yet
        $this->reservationService->autoTransition();
        // Reload reservation to get possibly updated status
        $reservation->refresh();

        $rooms = Room::query()
            ->with('facilities:id,name,slug')
            ->whereNull('deleted_at')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        $allFacilities = Facility::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $users = User::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.reservations.edit', [
            'reservation' => $reservation,
            'rooms' => $rooms,
            'users' => $users,
            'allFacilities' => $allFacilities,
        ]);
    }

    public function updateReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate(
            [
                'user_id' => 'nullable|string|exists:s_users,id',
                'room_id' => 'required|string|exists:m_rooms,id',
                'reservation_date' => 'required|date',
                'start_clock' => 'required|date_format:H:i',
                'end_clock' => 'required|date_format:H:i',
                'purpose' => 'nullable|string|max:1000',
                'visitor_count' => 'required|integer|min:1|max:1000',
            ],
            $this->reservationValidationMessages(),
            $this->reservationValidationAttributes()
        );

        [$startTime, $endTime] = $this->buildReservationDateTimes($validated['reservation_date'], $validated['start_clock'], $validated['end_clock']);

        if ($startTime->gte($endTime)) {
            return back()->withErrors(['end_clock' => WebMessages::RESERVATION_END_AFTER_START])->withInput();
        }

        if ($startTime->lte(now())) {
            return back()->withErrors(['start_clock' => WebMessages::RESERVATION_START_AFTER_NOW])->withInput();
        }

        try {
            $result = $this->reservationService->update($request->user(), $reservation, [
                'user_id' => $validated['user_id'] ?? $reservation->user_id,
                'room_id' => $validated['room_id'],
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'purpose' => $validated['purpose'] ?? null,
                'visitor_count' => (int) $validated['visitor_count'],
            ]);

            if (!$result['success']) {
                return back()->withErrors($this->formatReservationServiceErrors($result))->withInput();
            }

            return redirect()
                ->route('admin.reservations')
                ->with('success', WebMessages::RESERVATION_UPDATED_SUCCESS);
        } catch (Throwable $exception) {
            return back()->withErrors(['reservation' => WebMessages::RESERVATION_UPDATE_FAILED])->withInput();
        }
    }

    public function destroyReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $result = $this->reservationService->cancel($request->user(), $reservation);

        if (!$result['success']) {
            return back()->withErrors(['reservation' => $result['message']]);
        }

        return redirect()
            ->route('admin.reservations')
            ->with('success', WebMessages::RESERVATION_CANCELLED_SUCCESS);
    }

    public function completeReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $result = $this->reservationService->complete($request->user(), $reservation);

        if (!$result['success']) {
            return back()->withErrors(['reservation' => $result['message']]);
        }

        return redirect()
            ->route('admin.reservations')
            ->with('success', WebMessages::RESERVATION_COMPLETED_SUCCESS);
    }

    public function approvals(Request $request): View
    {
        $this->ensureAdminAccess($request);
        // Backstop: ensure pending reservations whose start_time has passed are cancelled
        // before showing the approvals list, same as reservations() page.
        $this->reservationService->autoTransition();

        $searchQuery = trim((string) $request->query('q', ''));

        $pendingReservationsQuery = Reservation::query()
            ->with(['room', 'user'])
            ->pending();

        if ($searchQuery !== '') {
            $pendingReservationsQuery->where(function ($query) use ($searchQuery) {
                $query->where('id', 'like', "%{$searchQuery}%")
                    ->orWhere('purpose', 'like', "%{$searchQuery}%")
                    ->orWhereHas('room', function ($roomQuery) use ($searchQuery) {
                        $roomQuery->where('name', 'like', "%{$searchQuery}%")
                            ->orWhereRaw('CAST(floor AS CHAR) LIKE ?', ["%{$searchQuery}%"]);
                    })
                    ->orWhereHas('user', function ($userQuery) use ($searchQuery) {
                        $userQuery->where('first_name', 'like', "%{$searchQuery}%")
                            ->orWhere('last_name', 'like', "%{$searchQuery}%")
                            ->orWhere('employee_id', 'like', "%{$searchQuery}%")
                            ->orWhere('email', 'like', "%{$searchQuery}%");
                    });
            });
        }

        $pendingReservations = $pendingReservationsQuery
            ->orderBy('start_time')
            ->paginate(10)
            ->withQueryString();

        $rooms = Room::query()
            ->whereNull('deleted_at')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        return view('admin.approvals.index', [
            'pendingReservations' => $pendingReservations,
            'searchQuery'         => $searchQuery,
            'rooms'               => $rooms,
        ]);
    }

    public function approveReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'room_id' => 'nullable|string|exists:m_rooms,id',
        ]);

        $targetRoomId = $validated['room_id'] ?? null;

        $result = $this->reservationService->approve($request->user(), $reservation, $targetRoomId);

        if (!$result['success']) {
            return back()->withErrors(['approval' => $result['message']]);
        }

        return redirect()
            ->route('admin.approvals')
            ->with('success', WebMessages::RESERVATION_APPROVED_SUCCESS);
    }

    public function rejectReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $result = $this->reservationService->reject($request->user(), $reservation);

        if (!$result['success']) {
            return back()->withErrors(['approval' => $result['message']]);
        }

        return redirect()
            ->route('admin.approvals')
            ->with('success', WebMessages::RESERVATION_REJECTED_SUCCESS);
    }

    private function reservationValidationMessages(): array
    {
        return WebMessages::RESERVATION_VALIDATION_MESSAGES;
    }

    private function reservationValidationAttributes(): array
    {
        return WebMessages::RESERVATION_VALIDATION_ATTRIBUTES;
    }

    /**
     * Format errors from ReservationService result into a key=>message array
     * suitable for withErrors().
     */
    private function formatReservationServiceErrors(array $result): array
    {
        $errors = [];

        // Top-level message always shown under the generic 'reservation' key
        $errors['reservation'] = $result['message'];

        // If CSP returned a list of constraint violations, surface them individually
        if (!empty($result['errors']['constraints'])) {
            foreach ($result['errors']['constraints'] as $index => $constraintError) {
                $errors["constraint_{$index}"] = $constraintError;
            }
        }

        return $errors;
    }

    private function buildReservationDateTimes(string $date, string $startClock, string $endClock): array
    {
        // Use the user's session timezone (or config default) for correct local→UTC conversion
        return TimezoneHelper::buildDateTimes($date, $startClock, $endClock, TimezoneHelper::getLocalTimezone());
    }

    public function setUserTimezone(Request $request): JsonResponse
    {
        $request->validate([
            'timezone' => 'required|string|timezone',
        ]);

        session(['user_timezone' => $request->input('timezone')]);

        return response()->json(['success' => true]);
    }
}
