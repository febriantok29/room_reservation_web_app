<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\RoomComplaint;
use App\Services\ImageService;
use App\Support\ReservationStatus;
use App\Support\WebMessages;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminComplaintController extends Controller
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    /**
     * Display a paginated list of all complaints.
     */
    public function index(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $searchQuery  = trim((string) $request->query('q', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $query = RoomComplaint::query()
            ->with(['reporter', 'room', 'facility'])
            ->orderByDesc('created_at');

        if ($searchQuery !== '') {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('id', 'like', "%{$searchQuery}%")
                    ->orWhere('title', 'like', "%{$searchQuery}%")
                    ->orWhereHas('reporter', fn ($u) => $u
                        ->where('first_name', 'like', "%{$searchQuery}%")
                        ->orWhere('last_name', 'like', "%{$searchQuery}%")
                        ->orWhere('employee_id', 'like', "%{$searchQuery}%"))
                    ->orWhereHas('room', fn ($r) => $r
                        ->where('name', 'like', "%{$searchQuery}%"));
            });
        }

        if (in_array($statusFilter, ['open', 'in_progress', 'resolved', 'rejected'], true)) {
            $query->status($statusFilter);
        }

        $complaints = $query->paginate(10)->withQueryString();

        return view('admin.complaints.index', [
            'complaints'   => $complaints,
            'searchQuery'  => $searchQuery,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Show the form to create a new complaint (admin on behalf of a user).
     */
    public function create(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $reservations = Reservation::query()
            ->with(['room', 'user'])
            ->where('status', ReservationStatus::Completed->value)
            ->orderByDesc('start_time')
            ->get();

        $facilities = Facility::query()->orderBy('name')->get();

        return view('admin.complaints.create', [
            'reservations' => $reservations,
            'facilities'   => $facilities,
        ]);
    }

    /**
     * Persist a new complaint submitted from the web form.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
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

        $reservation = Reservation::query()
            ->where('id', $validated['reservation_id'])
            ->where('status', ReservationStatus::Completed->value)
            ->first();

        if (!$reservation) {
            return back()
                ->withInput()
                ->withErrors(['reservation_id' => 'Hanya reservasi yang sudah selesai yang dapat dikomplain.']);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            try {
                $result = $this->imageService->upload($request->file('photo'), 'complaints');
                $photoPath = $result['path'];
            } catch (Throwable) {
                return back()
                    ->withInput()
                    ->withErrors(['photo' => 'Gagal mengunggah foto. Coba lagi.']);
            }
        }

        $complaint = RoomComplaint::create([
            'reservation_id' => $validated['reservation_id'],
            'reported_by'    => $reservation->user_id,
            'room_id'        => $reservation->room_id,
            'facility_id'    => $validated['facility_id'] ?? null,
            'title'          => $validated['title'],
            'description'    => $validated['description'],
            'photo_path'     => $photoPath,
            'status'         => 'open',
            'created_by'     => $request->user()->id,
            'updated_by'     => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.complaints.show', $complaint->id)
            ->with('success', WebMessages::COMPLAINT_CREATED_SUCCESS);
    }

    /**
     * Display the detail of a single complaint along with the status update form.
     */
    public function show(Request $request, RoomComplaint $complaint): View
    {
        $this->ensureAdminAccess($request);

        $complaint->load(['reporter', 'room', 'facility', 'reservation', 'resolver']);

        return view('admin.complaints.show', [
            'complaint' => $complaint,
        ]);
    }

    /**
     * Update the handling status of a complaint.
     */
    public function updateStatus(Request $request, RoomComplaint $complaint): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        if ($complaint->isClosed()) {
            return redirect()
                ->back()
                ->withErrors(['status' => WebMessages::COMPLAINT_ALREADY_CLOSED]);
        }

        $validated = $request->validate([
            'status'           => 'required|in:in_progress,resolved,rejected',
            'resolution_notes' => 'nullable|string|max:2000',
            'set_maintenance'  => 'nullable|boolean',
            'unset_maintenance' => 'nullable|boolean',
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in'       => 'Status tidak valid.',
        ]);

        $updates = [
            'status'           => $validated['status'],
            'resolution_notes' => $validated['resolution_notes'] ?? null,
            'updated_by'       => $request->user()->id,
        ];

        if (in_array($validated['status'], ['resolved', 'rejected'])) {
            $updates['resolved_at'] = Carbon::now();
            $updates['resolved_by'] = $request->user()->id;
        }

        $complaint->update($updates);

        if ($request->boolean('set_maintenance') && $complaint->room) {
            $complaint->room->update(['is_maintenance' => true]);
        } elseif ($request->boolean('unset_maintenance') && $complaint->room) {
            $complaint->room->update(['is_maintenance' => false]);
        }

        $message = match ($validated['status']) {
            'in_progress' => WebMessages::COMPLAINT_STATUS_IN_PROGRESS,
            'resolved'    => WebMessages::COMPLAINT_STATUS_RESOLVED,
            'rejected'    => WebMessages::COMPLAINT_STATUS_REJECTED,
            default       => 'Status komplain berhasil diperbarui.',
        };

        return redirect()
            ->route('admin.complaints.show', $complaint->id)
            ->with('success', $message);
    }

}
