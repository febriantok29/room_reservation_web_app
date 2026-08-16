<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Support\ReservationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Alat debug untuk testing alur status reservasi.
 *
 * Memungkinkan admin menggeser reservasi ke masa lalu agar bisa menguji
 * auto-transition (pending→cancelled, approved→completed) tanpa menunggu waktu.
 */
class AdminReservationDebugController extends Controller
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

    public function index(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $query = Reservation::query()
            ->with(['room', 'user'])
            ->orderBy('start_time', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return view('admin.tools.reservation-debug', [
            'reservations' => $query->paginate(15)->withQueryString(),
            'filterStatus' => $request->input('status', ''),
            'statuses'     => ReservationStatus::cases(),
        ]);
    }

    /**
     * Geser reservasi ke masa lalu: end_time = now - 1 jam,
     * start_time = end_time - durasi asli.
     */
    public function backdate(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $durationMinutes = $reservation->start_time->diffInMinutes($reservation->end_time);
        $newEnd = now()->subHour();
        $newStart = $newEnd->copy()->subMinutes($durationMinutes);

        $reservation->start_time = $newStart;
        $reservation->end_time = $newEnd;
        $reservation->updated_by = $request->user()?->id;
        $reservation->save();

        return back()->with('success', "Reservasi {$reservation->id} digeser ke masa lalu. Jalankan Auto-Transition untuk melihat perubahan status.");
    }

    /**
     * Jalankan auto-transition sekarang dan tampilkan hasilnya.
     */
    public function runTransition(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $counts = $this->reservationService->autoTransition();

        return back()->with('success', "Auto-transition selesai: {$counts['expired']} pending dibatalkan, {$counts['completed']} approved diselesaikan.");
    }
}
