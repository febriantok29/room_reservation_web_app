<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomComplaint;
use App\Models\User;
use App\Models\Division;
use App\Exports\ComplaintReportExport;
use App\Exports\DivisionActivityReportExport;
use App\Exports\DivisionUsageReportExport;
use App\Exports\MaintenanceReportExport;
use App\Exports\UsageReportExport;
use App\Exports\UserActivityReportExport;
use App\Exports\ScheduleHistoryReportExport;
use App\Exports\PeriodicReportExport;
use App\Support\ReservationStatus;
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

        $byFacility = $complaints->groupBy('facility_id')->map(function ($items) {
            $first = $items->first();
            $rooms = $items->groupBy('room_id')
                ->map(fn($ri) => $ri->first()->room?->name ?? null)
                ->filter()
                ->unique()
                ->values();
            return [
                'facility_name' => $first->facility?->name ?? 'Tanpa Fasilitas',
                'total'         => $items->count(),
                'open'          => $items->where('status', 'open')->count(),
                'in_progress'   => $items->where('status', 'in_progress')->count(),
                'resolved'      => $items->where('status', 'resolved')->count(),
                'rejected'      => $items->where('status', 'rejected')->count(),
                'rooms'         => $rooms,
            ];
        })->sortByDesc('total')->values();

        $summary = [
            'total'           => $complaints->count(),
            'open'            => $complaints->where('status', 'open')->count(),
            'in_progress'     => $complaints->where('status', 'in_progress')->count(),
            'resolved'        => $complaints->where('status', 'resolved')->count(),
            'rejected'        => $complaints->where('status', 'rejected')->count(),
            'total_facilities' => $byFacility->count(),
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

        return view('admin.reports.complaints', compact('complaints', 'byFacility', 'summary', 'rooms',
            'dateFrom', 'dateTo', 'statusFilter', 'roomFilter'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 5: Usage Recap
    // ─────────────────────────────────────────────────────────────────────────

    public function usage(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $roomFilter = $request->input('room_id', '');
        $format   = $request->input('format', '');

        $query = Reservation::query()
            ->with(['room:id,name,floor,capacity', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->whereIn('status', [ReservationStatus::Approved->value, ReservationStatus::Completed->value]);

        if ($dateFrom) {
            $query->where('start_time', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('start_time', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if ($roomFilter) {
            $query->where('room_id', $roomFilter);
        }

        $reservations = $query->orderBy('start_time')->get();

        $byRoom = $reservations->groupBy('room_id')->map(function ($items) {
            $first = $items->first();
            $totalMinutes = $items->sum(fn($r) => $r->start_time->diffInMinutes($r->end_time));
            $count = $items->count();
            return [
                'room_id'        => $first->room_id,
                'room_name'      => $first->room?->name ?? '-',
                'floor'          => $first->room?->floor ?? null,
                'capacity'       => $first->room?->capacity ?? '-',
                'reserved_count' => $count,
                'total_hours'    => round($totalMinutes / 60, 1),
                'avg_hours'      => $count > 0 ? round($totalMinutes / 60 / $count, 1) : 0,
                'total_visitors' => $items->sum('visitor_count'),
            ];
        })->values()->sortByDesc('total_hours')->values();

        $summary = [
            'date_from'          => $dateFrom,
            'date_to'            => $dateTo,
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

        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        $userFilter = $request->input('user_id', '');
        $format     = $request->input('format', '');

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->orderBy('start_time');

        if ($dateFrom) {
            $query->where('start_time', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('start_time', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        $reservations = $query->get();

        $byUser = $reservations->groupBy('user_id')->map(function ($items) {
            $first = $items->first();
            $totalMinutes = $items->sum(fn($r) => $r->start_time->diffInMinutes($r->end_time));
            return [
                'user_id'        => $first->user_id,
                'full_name'      => $first->user?->full_name ?? '-',
                'employee_id'    => $first->user?->employee_id ?? '-',
                'division_name'  => $first->user?->division?->name ?? 'Admin / Tanpa Divisi',
                'division_code'  => $first->user?->division?->code ?? '-',
                'total'          => $items->count(),
                ReservationStatus::Pending->value   => $items->where('status', ReservationStatus::Pending->value)->count(),
                ReservationStatus::Approved->value  => $items->where('status', ReservationStatus::Approved->value)->count(),
                ReservationStatus::Completed->value => $items->where('status', ReservationStatus::Completed->value)->count(),
                ReservationStatus::Rejected->value  => $items->where('status', ReservationStatus::Rejected->value)->count(),
                ReservationStatus::Cancelled->value => $items->where('status', ReservationStatus::Cancelled->value)->count(),
                'total_hours'    => round($totalMinutes / 60, 1),
                'total_visitors' => $items->sum('visitor_count'),
            ];
        })->values()->sortByDesc('total')->values();

        $summary = [
            'date_from'          => $dateFrom,
            'date_to'            => $dateTo,
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

        $dateFrom     = $request->input('date_from');
        $dateTo       = $request->input('date_to');
        $statusFilter = $request->input('status', '');
        $roomFilter   = $request->input('room_id', '');
        $format       = $request->input('format', '');

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->orderBy('start_time');

        if ($dateFrom) {
            $query->where('start_time', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('start_time', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($roomFilter) {
            $query->where('room_id', $roomFilter);
        }

        $reservations = $query->get();

        $byRoom = $reservations->groupBy('room_id')->map(function ($items) {
            $first = $items->first();
            $totalMinutes = $items->sum(fn($r) => $r->start_time->diffInMinutes($r->end_time));
            return [
                'room_name'      => $first->room?->name ?? '-',
                'floor'          => $first->room?->floor ?? null,
                'total'          => $items->count(),
                ReservationStatus::Pending->value   => $items->where('status', ReservationStatus::Pending->value)->count(),
                ReservationStatus::Approved->value  => $items->where('status', ReservationStatus::Approved->value)->count(),
                ReservationStatus::Completed->value => $items->where('status', ReservationStatus::Completed->value)->count(),
                ReservationStatus::Rejected->value  => $items->where('status', ReservationStatus::Rejected->value)->count(),
                ReservationStatus::Cancelled->value => $items->where('status', ReservationStatus::Cancelled->value)->count(),
                'total_hours'    => round($totalMinutes / 60, 1),
                'total_visitors' => $items->sum('visitor_count'),
            ];
        })->sortByDesc('total')->values();

        $summary = [
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
            'total'          => $reservations->count(),
            ReservationStatus::Pending->value   => $reservations->where('status', ReservationStatus::Pending->value)->count(),
            ReservationStatus::Approved->value  => $reservations->where('status', ReservationStatus::Approved->value)->count(),
            ReservationStatus::Completed->value => $reservations->where('status', ReservationStatus::Completed->value)->count(),
            ReservationStatus::Rejected->value  => $reservations->where('status', ReservationStatus::Rejected->value)->count(),
            ReservationStatus::Cancelled->value => $reservations->where('status', ReservationStatus::Cancelled->value)->count(),
            'total_rooms'    => $byRoom->count(),
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

        return view('admin.reports.schedule-history', compact('reservations', 'byRoom', 'summary', 'rooms',
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
                ReservationStatus::Approved->value  => $items->where('status', ReservationStatus::Approved->value)->count(),
                ReservationStatus::Completed->value => $items->where('status', ReservationStatus::Completed->value)->count(),
                ReservationStatus::Rejected->value  => $items->where('status', ReservationStatus::Rejected->value)->count(),
                ReservationStatus::Cancelled->value => $items->where('status', ReservationStatus::Cancelled->value)->count(),
                ReservationStatus::Pending->value   => $items->where('status', ReservationStatus::Pending->value)->count(),
                'visitors'  => $items->sum('visitor_count'),
            ];
        })->values();

        $summary = [
            'period'    => $period,
            'year'      => $year,
            'month'     => $period === 'daily' ? $month : null,
            'total'     => $reservations->count(),
            ReservationStatus::Completed->value => $reservations->where('status', ReservationStatus::Completed->value)->count(),
            ReservationStatus::Approved->value  => $reservations->where('status', ReservationStatus::Approved->value)->count(),
            ReservationStatus::Rejected->value  => $reservations->where('status', ReservationStatus::Rejected->value)->count(),
            ReservationStatus::Cancelled->value => $reservations->where('status', ReservationStatus::Cancelled->value)->count(),
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

    // ─────────────────────────────────────────────────────────────────────────
    // Output 9: Division Activity Report
    // ─────────────────────────────────────────────────────────────────────────

    public function divisionActivity(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $format   = $request->input('format', '');

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->orderBy('start_time');

        if ($dateFrom) {
            $query->where('start_time', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('start_time', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $reservations = $query->get();

        $byDivision = $reservations->groupBy(fn($r) => $r->user?->division_id ?? '__no_division__')
            ->map(function ($items) {
                $first      = $items->first();
                $division   = $first->user?->division;
                return [
                    'division_id'   => $division?->id ?? null,
                    'division_name' => $division?->name ?? 'Admin / Tanpa Divisi',
                    'division_code' => $division?->code ?? '-',
                    'total'         => $items->count(),
                    ReservationStatus::Approved->value  => $items->where('status', ReservationStatus::Approved->value)->count(),
                    ReservationStatus::Completed->value => $items->where('status', ReservationStatus::Completed->value)->count(),
                    ReservationStatus::Rejected->value  => $items->where('status', ReservationStatus::Rejected->value)->count(),
                    ReservationStatus::Cancelled->value => $items->where('status', ReservationStatus::Cancelled->value)->count(),
                    ReservationStatus::Pending->value   => $items->where('status', ReservationStatus::Pending->value)->count(),
                    'visitors'      => $items->sum('visitor_count'),
                ];
            })->values()->sortByDesc('total')->values();

        $summary = [
            'date_from'          => $dateFrom,
            'date_to'            => $dateTo,
            'total_reservations' => $reservations->count(),
            'total_visitors'     => $reservations->sum('visitor_count'),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.division-activity', compact('reservations', 'byDivision', 'summary'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-aktivitas-divisi-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new DivisionActivityReportExport($reservations, $byDivision, $summary),
                'laporan-aktivitas-divisi-' . now()->format('Ymd') . '.xlsx'
            );
        }

        return view('admin.reports.division-activity', compact('reservations', 'byDivision', 'summary',
            'dateFrom', 'dateTo'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 10: Maintenance & Room Status Report
    // ─────────────────────────────────────────────────────────────────────────

    public function maintenance(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        $roomFilter = $request->input('room_id', '');
        $format     = $request->input('format', '');

        $roomQuery = Room::query()
            ->whereNull('deleted_at')
            ->orderBy('floor')
            ->orderBy('name');

        if ($roomFilter) {
            $roomQuery->where('id', $roomFilter);
        }

        $allRooms = $roomQuery->get();

        $complaintQuery = RoomComplaint::query()
            ->whereNull('deleted_at');

        if ($dateFrom) {
            $complaintQuery->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $complaintQuery->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $complaints = $complaintQuery
            ->when($roomFilter, fn($q) => $q->where('room_id', $roomFilter))
            ->get()
            ->groupBy('room_id');

        $rooms = $allRooms->map(function ($room) use ($complaints) {
            $roomComplaints = $complaints->get($room->id, collect());
            return [
                'id'               => $room->id,
                'name'             => $room->name,
                'floor'            => $room->floor,
                'capacity'         => $room->capacity,
                'is_maintenance'   => (bool) $room->is_maintenance,
                'total_complaints' => $roomComplaints->count(),
                'open'             => $roomComplaints->where('status', 'open')->count(),
                'in_progress'      => $roomComplaints->where('status', 'in_progress')->count(),
                'resolved'         => $roomComplaints->where('status', 'resolved')->count(),
                'rejected'         => $roomComplaints->where('status', 'rejected')->count(),
            ];
        });

        $allComplaints = $complaints->flatten(1);

        $summary = [
            'date_from'           => $dateFrom,
            'date_to'             => $dateTo,
            'total_rooms'         => $allRooms->count(),
            'under_maintenance'   => $allRooms->where('is_maintenance', true)->count(),
            'total_complaints'    => $allComplaints->count(),
            'open_complaints'     => $allComplaints->where('status', 'open')->count(),
            'resolved_complaints' => $allComplaints->where('status', 'resolved')->count(),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.maintenance', compact('rooms', 'summary'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-maintenance-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new MaintenanceReportExport($rooms, $summary),
                'laporan-maintenance-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $allRoomsList = Room::query()->whereNull('deleted_at')->orderBy('floor')->orderBy('name')->get();

        return view('admin.reports.maintenance', compact('rooms', 'summary', 'allRoomsList',
            'dateFrom', 'dateTo', 'roomFilter'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output 11: Division Room Usage Report
    // ─────────────────────────────────────────────────────────────────────────

    public function divisionUsage(Request $request): mixed
    {
        $this->ensureAdminAccess($request);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $format   = $request->input('format', '');

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->whereIn('status', [ReservationStatus::Approved->value, ReservationStatus::Completed->value])
            ->orderBy('start_time');

        if ($dateFrom) {
            $query->where('start_time', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('start_time', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $reservations = $query->get();

        $byDivision = $reservations->groupBy(fn($r) => $r->user?->division_id ?? '__no_division__')
            ->map(function ($items) {
                $first    = $items->first();
                $division = $first->user?->division;

                $totalMinutes = $items->sum(fn($r) => $r->start_time->diffInMinutes($r->end_time));
                $totalHours   = round($totalMinutes / 60, 1);
                $count        = $items->count();

                $roomBreakdown = $items->groupBy('room_id')->map(function ($roomItems) {
                    $r    = $roomItems->first();
                    $mins = $roomItems->sum(fn($ri) => $ri->start_time->diffInMinutes($ri->end_time));
                    return [
                        'room_name' => $r->room?->name ?? '-',
                        'floor'     => $r->room?->floor ?? '-',
                        'count'     => $roomItems->count(),
                        'hours'     => round($mins / 60, 1),
                        'visitors'  => $roomItems->sum('visitor_count'),
                    ];
                })->values()->sortByDesc('hours')->values()->toArray();

                return [
                    'division_id'      => $division?->id ?? null,
                    'division_name'    => $division?->name ?? 'Admin / Tanpa Divisi',
                    'division_code'    => $division?->code ?? '-',
                    'reservation_count' => $count,
                    'total_hours'      => $totalHours,
                    'avg_hours'        => $count > 0 ? round($totalHours / $count, 1) : 0,
                    'total_visitors'   => $items->sum('visitor_count'),
                    'rooms_used'       => collect($roomBreakdown)->pluck('room_name')->unique()->values()->toArray(),
                    'room_breakdown'   => $roomBreakdown,
                ];
            })->values()->sortByDesc('total_hours')->values();

        $summary = [
            'date_from'          => $dateFrom,
            'date_to'            => $dateTo,
            'total_reservations' => $reservations->count(),
            'total_hours'        => round($reservations->sum(fn($r) => $r->start_time->diffInMinutes($r->end_time)) / 60, 1),
            'total_visitors'     => $reservations->sum('visitor_count'),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.division-usage', compact('byDivision', 'summary'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-pemakaian-divisi-' . now()->format('Ymd') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new DivisionUsageReportExport($byDivision, $summary),
                'laporan-pemakaian-divisi-' . now()->format('Ymd') . '.xlsx'
            );
        }

        return view('admin.reports.division-usage', compact('byDivision', 'summary', 'dateFrom', 'dateTo'));
    }
}
