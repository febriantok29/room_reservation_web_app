<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomComplaint;
use App\Models\User;
use App\Exports\ComplaintReportExport;
use App\Exports\UsageReportExport;
use App\Exports\UserActivityReportExport;
use App\Exports\ScheduleHistoryReportExport;
use App\Exports\PeriodicReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AdminReportController extends Controller
{
    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 3: Complaint Report
    // ─────────────────────────────────────────────────────────────────────────

    public function complaints(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom    = $request->input('date_from');
        $dateTo      = $request->input('date_to');
        $statusFilter = $request->input('status', '');
        $roomFilter  = $request->input('room_id', '');
        $format      = $request->input('format', '');

        $query = RoomComplaint::query()
            ->with(['room', 'reporter', 'facility', 'resolver'])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($roomFilter) {
            $query->where('room_id', $roomFilter);
        }

        $complaints = $query->get();

        $summary = [
            'total'       => $complaints->count(),
            'open'        => $complaints->where('status', 'open')->count(),
            'in_progress' => $complaints->where('status', 'in_progress')->count(),
            'resolved'    => $complaints->where('status', 'resolved')->count(),
            'rejected'    => $complaints->where('status', 'rejected')->count(),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.complaints', compact('complaints', 'summary', 'request'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-komplain-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new ComplaintReportExport($complaints, $summary),
                'laporan-komplain-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $rooms = Room::query()->whereNull('deleted_at')->orderBy('name')->get();

        return view('admin.reports.complaints', compact('complaints', 'summary', 'rooms',
            'dateFrom', 'dateTo', 'statusFilter', 'roomFilter'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 5: Usage Recap
    // ─────────────────────────────────────────────────────────────────────────

    public function usage(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', Carbon::now()->toDateString());
        $roomFilter = $request->input('room_id', '');
        $format   = $request->input('format', '');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->endOfDay();

        $query = Reservation::query()
            ->with(['room:id,name,floor,capacity', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->whereIn('status', ['approved', 'completed'])
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to);

        if ($roomFilter) {
            $query->where('room_id', $roomFilter);
        }

        $reservations = $query->orderBy('start_time')->get();

        $byRoom = $reservations->groupBy('room_id')->map(function ($items) {
            $first = $items->first();
            $totalMinutes = $items->sum(fn($r) => $r->start_time->diffInMinutes($r->end_time));
            return [
                'room_id'        => $first->room_id,
                'room_name'      => $first->room?->name ?? '-',
                'floor'          => $first->room?->floor ?? null,
                'reserved_count' => $items->count(),
                'total_hours'    => round($totalMinutes / 60, 1),
                'total_visitors' => $items->sum('visitor_count'),
            ];
        })->values();

        $summary = [
            'date_from'          => $from->toDateString(),
            'date_to'            => $to->toDateString(),
            'total_reservations' => $reservations->count(),
            'total_rooms_used'   => $byRoom->count(),
            'total_visitors'     => $reservations->sum('visitor_count'),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.usage', compact('reservations', 'byRoom', 'summary'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-penggunaan-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new UsageReportExport($reservations, $byRoom, $summary),
                'laporan-penggunaan-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $rooms = Room::query()->whereNull('deleted_at')->orderBy('floor')->orderBy('name')->get();

        return view('admin.reports.usage', compact('reservations', 'byRoom', 'summary', 'rooms',
            'dateFrom', 'dateTo', 'roomFilter'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 6: Per-User Activity
    // ─────────────────────────────────────────────────────────────────────────

    public function userActivity(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom   = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo     = $request->input('date_to', Carbon::now()->toDateString());
        $userFilter = $request->input('user_id', '');
        $format     = $request->input('format', '');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->endOfDay();

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time');

        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        $reservations = $query->get();

        $byUser = $reservations->groupBy('user_id')->map(function ($items) {
            $first = $items->first();
            return [
                'user_id'     => $first->user_id,
                'full_name'   => $first->user?->full_name ?? '-',
                'employee_id' => $first->user?->employee_id ?? '-',
                'total'       => $items->count(),
                'pending'     => $items->where('status', 'pending')->count(),
                'approved'    => $items->where('status', 'approved')->count(),
                'completed'   => $items->where('status', 'completed')->count(),
                'rejected'    => $items->where('status', 'rejected')->count(),
                'cancelled'   => $items->where('status', 'cancelled')->count(),
            ];
        })->values();

        $summary = [
            'date_from'          => $from->toDateString(),
            'date_to'            => $to->toDateString(),
            'total_reservations' => $reservations->count(),
            'total_users'        => $byUser->count(),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.user-activity', compact('reservations', 'byUser', 'summary'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-aktivitas-pengguna-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new UserActivityReportExport($reservations, $byUser, $summary),
                'laporan-aktivitas-pengguna-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $users = User::query()->whereNull('deleted_at')->orderBy('first_name')->get();

        return view('admin.reports.user-activity', compact('reservations', 'byUser', 'summary', 'users',
            'dateFrom', 'dateTo', 'userFilter'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 7: Schedule & History
    // ─────────────────────────────────────────────────────────────────────────

    public function scheduleHistory(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom     = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo       = $request->input('date_to', Carbon::now()->toDateString());
        $statusFilter = $request->input('status', '');
        $roomFilter   = $request->input('room_id', '');
        $format       = $request->input('format', '');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->endOfDay();

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($roomFilter) {
            $query->where('room_id', $roomFilter);
        }

        $reservations = $query->get();

        $summary = [
            'date_from'  => $from->toDateString(),
            'date_to'    => $to->toDateString(),
            'total'      => $reservations->count(),
            'pending'    => $reservations->where('status', 'pending')->count(),
            'approved'   => $reservations->where('status', 'approved')->count(),
            'completed'  => $reservations->where('status', 'completed')->count(),
            'rejected'   => $reservations->where('status', 'rejected')->count(),
            'cancelled'  => $reservations->where('status', 'cancelled')->count(),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.schedule-history', compact('reservations', 'summary'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-jadwal-histori-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new ScheduleHistoryReportExport($reservations, $summary),
                'laporan-jadwal-histori-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $rooms = Room::query()->whereNull('deleted_at')->orderBy('floor')->orderBy('name')->get();

        return view('admin.reports.schedule-history', compact('reservations', 'summary', 'rooms',
            'dateFrom', 'dateTo', 'statusFilter', 'roomFilter'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 8: Periodic Summary
    // ─────────────────────────────────────────────────────────────────────────

    public function periodic(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $period = $request->input('period', 'monthly');
        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);
        $format = $request->input('format', '');

        if ($period === 'daily') {
            $from    = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $to      = $from->copy()->endOfMonth()->endOfDay();
            $groupBy = fn($r) => $r->start_time->toDateString();
            $labelKey = 'date';
        } elseif ($period === 'weekly') {
            $from    = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $to      = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $groupBy = fn($r) => 'Week ' . $r->start_time->isoWeek;
            $labelKey = 'week';
        } else {
            $from    = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $to      = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $groupBy = fn($r) => $r->start_time->format('Y-m');
            $labelKey = 'month';
        }

        $reservations = Reservation::query()
            ->whereNull('deleted_at')
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time')
            ->get();

        $grouped = $reservations->groupBy($groupBy)->map(function ($items, $key) use ($labelKey) {
            return [
                $labelKey   => $key,
                'total'     => $items->count(),
                'approved'  => $items->where('status', 'approved')->count(),
                'completed' => $items->where('status', 'completed')->count(),
                'rejected'  => $items->where('status', 'rejected')->count(),
                'cancelled' => $items->where('status', 'cancelled')->count(),
                'pending'   => $items->where('status', 'pending')->count(),
                'visitors'  => $items->sum('visitor_count'),
            ];
        })->values();

        $summary = [
            'period'    => $period,
            'year'      => $year,
            'month'     => $period === 'daily' ? $month : null,
            'total'     => $reservations->count(),
            'completed' => $reservations->where('status', 'completed')->count(),
            'approved'  => $reservations->where('status', 'approved')->count(),
            'rejected'  => $reservations->where('status', 'rejected')->count(),
            'cancelled' => $reservations->where('status', 'cancelled')->count(),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.periodic', compact('grouped', 'summary', 'period', 'year', 'month'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('laporan-periodik-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new PeriodicReportExport($grouped, $summary),
                'laporan-periodik-' . now()->format('Ymd') . '.xlsx'
            );
        }

        return view('admin.reports.periodic', compact('grouped', 'summary', 'period', 'year', 'month'));
    }
}
