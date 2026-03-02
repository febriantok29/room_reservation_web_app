<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function dashboard(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $summary = [
            'total_rooms' => Room::query()->whereNull('deleted_at')->count(),
            'total_users' => User::query()->whereNull('deleted_at')->count(),
            'pending_reservations' => Reservation::query()->pending()->count(),
            'approved_reservations' => Reservation::query()->approved()->count(),
        ];

        $latestReservations = Reservation::query()
            ->with(['room', 'user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'summary' => $summary,
            'latestReservations' => $latestReservations,
        ]);
    }

    public function rooms(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $rooms = Room::query()
            ->whereNull('deleted_at')
            ->orderBy('location')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    public function createRoom(Request $request): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.rooms.create');
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'required|string|max:100',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1|max:1000',
            'is_maintenance' => 'nullable|boolean',
        ]);

        $room = new Room();
        $room->fill($validated);
        $room->is_maintenance = $request->boolean('is_maintenance', false);
        $room->created_by = $request->user()->id;
        $room->updated_by = $request->user()->id;
        $room->save();

        return redirect()
            ->route('admin.rooms')
            ->with('success', 'Data ruangan berhasil ditambahkan.');
    }

    public function editRoom(Request $request, Room $room): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.rooms.edit', [
            'room' => $room,
        ]);
    }

    public function updateRoom(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'required|string|max:100',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1|max:1000',
            'is_maintenance' => 'nullable|boolean',
        ]);

        $room->fill($validated);
        $room->is_maintenance = $request->boolean('is_maintenance', false);
        $room->updated_by = $request->user()->id;
        $room->save();

        return redirect()
            ->route('admin.rooms')
            ->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroyRoom(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $room->deleted_by = $request->user()->id;
        $room->save();
        $room->delete();

        return redirect()
            ->route('admin.rooms')
            ->with('success', 'Data ruangan berhasil dihapus.');
    }

    public function reservations(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $reservations = Reservation::query()
            ->with(['room', 'user'])
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('admin.reservations.index', [
            'reservations' => $reservations,
        ]);
    }

    public function createReservation(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $rooms = Room::query()
            ->whereNull('deleted_at')
            ->orderBy('location')
            ->orderBy('name')
            ->get();

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
            return back()->withErrors(['end_clock' => 'Jam selesai harus setelah jam mulai.'])->withInput();
        }

        if ($startTime->lte(now())) {
            return back()->withErrors(['start_clock' => 'Waktu mulai harus lebih dari waktu saat ini.'])->withInput();
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
                ->with('success', 'Reservasi berhasil ditambahkan.');
        } catch (\Throwable $exception) {
            return back()->withErrors(['reservation' => 'Terjadi kesalahan saat menyimpan reservasi.'])->withInput();
        }
    }

    public function editReservation(Request $request, Reservation $reservation): View
    {
        $this->ensureAdminAccess($request);

        $rooms = Room::query()
            ->whereNull('deleted_at')
            ->orderBy('location')
            ->orderBy('name')
            ->get();

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
            return back()->withErrors(['end_clock' => 'Jam selesai harus setelah jam mulai.'])->withInput();
        }

        if ($startTime->lte(now())) {
            return back()->withErrors(['start_clock' => 'Waktu mulai harus lebih dari waktu saat ini.'])->withInput();
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
                ->with('success', 'Reservasi berhasil diperbarui.');
        } catch (\Throwable $exception) {
            return back()->withErrors(['reservation' => 'Terjadi kesalahan saat memperbarui reservasi.'])->withInput();
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
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }

    public function approvals(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $pendingReservations = Reservation::query()
            ->with(['room', 'user'])
            ->pending()
            ->orderBy('start_time')
            ->paginate(10);

        return view('admin.approvals.index', [
            'pendingReservations' => $pendingReservations,
        ]);
    }

    public function approveReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $result = $this->reservationService->approve($request->user(), $reservation);

        if (!$result['success']) {
            return back()->withErrors(['approval' => $result['message']]);
        }

        return redirect()
            ->route('admin.approvals')
            ->with('success', 'Reservasi berhasil disetujui.');
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
            ->with('success', 'Reservasi berhasil ditolak.');
    }

    private function reservationValidationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'date_format' => ':attribute harus menggunakan format HH:MM.',
            'integer' => ':attribute harus berupa angka bulat.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'string' => ':attribute harus berupa teks.',
        ];
    }

    private function reservationValidationAttributes(): array
    {
        return [
            'user_id' => 'pegawai',
            'room_id' => 'ruangan',
            'reservation_date' => 'tanggal reservasi',
            'start_clock' => 'jam mulai',
            'end_clock' => 'jam selesai',
            'purpose' => 'tujuan',
            'visitor_count' => 'jumlah pengunjung',
        ];
    }

    private function buildReservationDateTimes(string $date, string $startClock, string $endClock): array
    {
        $startTime = Carbon::parse($date . ' ' . $startClock . ':00');
        $endTime = Carbon::parse($date . ' ' . $endClock . ':00');

        return [$startTime, $endTime];
    }

    private function formatReservationServiceErrors(array $result): array
    {
        if (empty($result['errors']['constraints']) || !is_array($result['errors']['constraints'])) {
            return ['reservation' => $result['message'] ?? 'Terjadi kesalahan pada data reservasi.'];
        }

        $translated = array_map(function (string $message) {
            return match (true) {
                str_contains($message, 'Start time must be before end time') => 'Waktu mulai harus sebelum waktu selesai.',
                str_contains($message, 'Cannot make reservations for past time') => 'Tidak bisa membuat reservasi pada waktu yang sudah lewat.',
                str_contains($message, 'Room does not exist or has been deleted') => 'Ruangan tidak ditemukan atau sudah dihapus.',
                str_contains($message, 'Room is currently under maintenance') => 'Ruangan sedang dalam maintenance.',
                str_contains($message, 'Visitor count') && str_contains($message, 'exceeds room capacity') => 'Jumlah pengunjung melebihi kapasitas ruangan.',
                str_contains($message, 'Time slot is not available') => 'Slot waktu tidak tersedia karena bentrok dengan reservasi lain.',
                default => 'Data reservasi tidak memenuhi aturan penjadwalan.',
            };
        }, $result['errors']['constraints']);

        return ['reservation' => implode(' ', array_unique($translated))];
    }
}
