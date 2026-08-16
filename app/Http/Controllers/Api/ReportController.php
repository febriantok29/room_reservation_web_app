<?php

namespace App\Http\Controllers\Api;

use App\Exports\ComplaintReportExport;
use App\Exports\DivisionActivityReportExport;
use App\Exports\DivisionUsageReportExport;
use App\Exports\MaintenanceReportExport;
use App\Exports\PeriodicReportExport;
use App\Exports\ScheduleHistoryReportExport;
use App\Exports\UsageReportExport;
use App\Exports\UserActivityReportExport;
use App\Http\Controllers\Concerns\NormalizesFilterValues;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Division;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomComplaint;
use App\Models\User;
use App\Support\ApiMessages;
use App\Support\ReservationStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use NormalizesFilterValues;

    /**
     * Output 3 — Complaint & Facility Damage Report
     * GET /v1/reports/complaints?format=json|pdf|excel&date_from=&date_to=&status=&room_id=
     */
    public function complaints(Request $request): mixed
    {
        $statusFilters = $this->normalizeFilterValues($request->input('status'));
        $roomFilters = $this->normalizeFilterValues($request->input('room_id'));

        $validator = Validator::make([
            ...$request->all(),
            'status' => $statusFilters,
            'room_id' => $roomFilters,
        ], [
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|array',
            'status.*' => 'in:open,in_progress,resolved,rejected',
            'room_id' => 'nullable|array',
            'room_id.*' => 'string|exists:m_rooms,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = RoomComplaint::query()
            ->with(['room', 'reporter', 'facility', 'resolver'])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }
        if ($statusFilters !== []) {
            $query->whereIn('status', $statusFilters);
        }
        if ($roomFilters !== []) {
            $query->whereIn('room_id', $roomFilters);
        }

        $complaints = $query->get();

        $summary = [
            'total' => $complaints->count(),
            'open' => $complaints->where('status', 'open')->count(),
            'in_progress' => $complaints->where('status', 'in_progress')->count(),
            'resolved' => $complaints->where('status', 'resolved')->count(),
            'rejected' => $complaints->where('status', 'rejected')->count(),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.complaints', compact('complaints', 'summary', 'request'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-komplain-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new ComplaintReportExport($complaints, $summary),
                'laporan-komplain-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'complaints' => $complaints,
        ], ApiMessages::REPORT_COMPLAINT_SUCCESS);
    }

    /**
     * Output 5 — Rekapitulasi Penggunaan Ruangan
     * GET /v1/reports/usage?format=json|pdf|excel&date_from=&date_to=&room_id=
     */
    public function usage(Request $request): mixed
    {
        $roomFilters = $this->normalizeFilterValues($request->input('room_id'));

        $validator = Validator::make([
            ...$request->all(),
            'room_id' => $roomFilters,
        ], [
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'room_id' => 'nullable|array',
            'room_id.*' => 'string|exists:m_rooms,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Reservation::query()
            ->with(['room:id,name,floor,capacity', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->whereIn('status', [ReservationStatus::Approved->value, ReservationStatus::Completed->value]);

        if ($request->filled('date_from')) {
            $query->where('start_time', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('start_time', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        if ($roomFilters !== []) {
            $query->whereIn('room_id', $roomFilters);
        }

        $reservations = $query->orderBy('start_time')->get();

        // Aggregate per room
        $byRoom = $reservations->groupBy('room_id')->map(function ($items) {
            $first = $items->first();
            $totalMinutes = $items->sum(fn ($r) => $r->start_time->diffInMinutes($r->end_time));

            return [
                'room_id' => $first->room_id,
                'room_name' => $first->room?->name ?? '-',
                'floor' => $first->room?->floor ?? null,
                'reserved_count' => $items->count(),
                'total_hours' => round($totalMinutes / 60, 1),
                'total_visitors' => $items->sum('visitor_count'),
            ];
        })->values();

        $summary = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'total_reservations' => $reservations->count(),
            'total_rooms_used' => $byRoom->count(),
            'total_visitors' => $reservations->sum('visitor_count'),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.usage', compact('reservations', 'byRoom', 'summary'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-penggunaan-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new UsageReportExport($reservations, $byRoom, $summary),
                'laporan-penggunaan-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'by_room' => $byRoom,
            'reservations' => $reservations,
        ], ApiMessages::REPORT_USAGE_SUCCESS);
    }

    /**
     * Output 6 — Laporan Aktivitas Reservasi per Pengguna
     * GET /v1/reports/user-activity?format=json|pdf|excel&date_from=&date_to=&user_id=
     */
    public function userActivity(Request $request): mixed
    {
        $userFilters = $this->normalizeFilterValues($request->input('user_id'));

        $validator = Validator::make([
            ...$request->all(),
            'user_id' => $userFilters,
        ], [
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'user_id' => 'nullable|array',
            'user_id.*' => 'string|exists:s_users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $actor = $request->user();

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time');

        // Non-admins can only see their own
        if (! $actor->canApprove()) {
            $query->where('user_id', $actor->id);
        } elseif ($userFilters !== []) {
            $query->whereIn('user_id', $userFilters);
        }

        $reservations = $query->get();

        // Aggregate per user
        $byUser = $reservations->groupBy('user_id')->map(function ($items) {
            $first = $items->first();

            return [
                'user_id' => $first->user_id,
                'full_name' => $first->user?->full_name ?? '-',
                'employee_id' => $first->user?->employee_id ?? '-',
                'division_name' => $first->user?->division?->name ?? 'Admin / Tanpa Divisi',
                'division_code' => $first->user?->division?->code ?? '-',
                'total' => $items->count(),
                ReservationStatus::Pending->value => $items->where('status', ReservationStatus::Pending->value)->count(),
                ReservationStatus::Approved->value => $items->where('status', ReservationStatus::Approved->value)->count(),
                ReservationStatus::Completed->value => $items->where('status', ReservationStatus::Completed->value)->count(),
                ReservationStatus::Rejected->value => $items->where('status', ReservationStatus::Rejected->value)->count(),
                ReservationStatus::Cancelled->value => $items->where('status', ReservationStatus::Cancelled->value)->count(),
            ];
        })->values();

        $summary = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'total_reservations' => $reservations->count(),
            'total_users' => $byUser->count(),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.user-activity', compact('reservations', 'byUser', 'summary'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-aktivitas-pengguna-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new UserActivityReportExport($reservations, $byUser, $summary),
                'laporan-aktivitas-pengguna-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'by_user' => $byUser,
            'reservations' => $reservations,
        ], ApiMessages::REPORT_USER_ACTIVITY_SUCCESS);
    }

    /**
     * Output 7 — Laporan Jadwal & Histori Reservasi
     * GET /v1/reports/schedule-history?format=json|pdf|excel&date_from=&date_to=&status=&room_id=
     */
    public function scheduleHistory(Request $request): mixed
    {
        $statusFilters = $this->normalizeFilterValues($request->input('status'));
        $roomFilters = $this->normalizeFilterValues($request->input('room_id'));

        $validator = Validator::make([
            ...$request->all(),
            'status' => $statusFilters,
            'room_id' => $roomFilters,
        ], [
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|array',
            'status.*' => 'in:pending,approved,rejected,completed,cancelled',
            'room_id' => 'nullable|array',
            'room_id.*' => 'string|exists:m_rooms,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $actor = $request->user();

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time');

        if (! $actor->canApprove()) {
            $query->where('user_id', $actor->id);
        }
        if ($statusFilters !== []) {
            $query->whereIn('status', $statusFilters);
        }
        if ($roomFilters !== []) {
            $query->whereIn('room_id', $roomFilters);
        }

        $reservations = $query->get();

        $summary = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'total' => $reservations->count(),
            ReservationStatus::Pending->value => $reservations->where('status', ReservationStatus::Pending->value)->count(),
            ReservationStatus::Approved->value => $reservations->where('status', ReservationStatus::Approved->value)->count(),
            ReservationStatus::Completed->value => $reservations->where('status', ReservationStatus::Completed->value)->count(),
            ReservationStatus::Rejected->value => $reservations->where('status', ReservationStatus::Rejected->value)->count(),
            ReservationStatus::Cancelled->value => $reservations->where('status', ReservationStatus::Cancelled->value)->count(),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.schedule-history', compact('reservations', 'summary'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-jadwal-histori-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new ScheduleHistoryReportExport($reservations, $summary),
                'laporan-jadwal-histori-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'reservations' => $reservations,
        ], ApiMessages::REPORT_SCHEDULE_HISTORY_SUCCESS);
    }

    /**
     * Output 8 — Laporan Ringkasan Periodik Reservasi
     * GET /v1/reports/periodic?format=json|pdf|excel&period=monthly|weekly|daily&year=&month=
     */
    public function periodic(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'format' => 'nullable|in:json,pdf,excel',
            'period' => 'nullable|in:daily,weekly,monthly',
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $period = $request->input('period', 'monthly');
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        if ($period === 'daily') {
            $from = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $to = $from->copy()->endOfMonth()->endOfDay();
            $groupBy = fn ($r) => $r->start_time->toDateString();
            $labelKey = 'date';
        } elseif ($period === 'weekly') {
            $from = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $to = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $groupBy = fn ($r) => 'Week '.$r->start_time->isoWeek;
            $labelKey = 'week';
        } else {
            // monthly — show all months of the year
            $from = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $to = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $groupBy = fn ($r) => $r->start_time->format('Y-m');
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
                $labelKey => $key,
                'total' => $items->count(),
                ReservationStatus::Approved->value => $items->where('status', ReservationStatus::Approved->value)->count(),
                ReservationStatus::Completed->value => $items->where('status', ReservationStatus::Completed->value)->count(),
                ReservationStatus::Rejected->value => $items->where('status', ReservationStatus::Rejected->value)->count(),
                ReservationStatus::Cancelled->value => $items->where('status', ReservationStatus::Cancelled->value)->count(),
                ReservationStatus::Pending->value => $items->where('status', ReservationStatus::Pending->value)->count(),
                'visitors' => $items->sum('visitor_count'),
            ];
        })->values();

        $summary = [
            'period' => $period,
            'year' => $year,
            'month' => $period === 'daily' ? $month : null,
            'total' => $reservations->count(),
            ReservationStatus::Completed->value => $reservations->where('status', ReservationStatus::Completed->value)->count(),
            ReservationStatus::Approved->value => $reservations->where('status', ReservationStatus::Approved->value)->count(),
            ReservationStatus::Rejected->value => $reservations->where('status', ReservationStatus::Rejected->value)->count(),
            ReservationStatus::Cancelled->value => $reservations->where('status', ReservationStatus::Cancelled->value)->count(),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.periodic', compact('grouped', 'summary', 'period', 'year', 'month'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('laporan-periodik-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new PeriodicReportExport($grouped, $summary),
                'laporan-periodik-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'data' => $grouped,
        ], ApiMessages::REPORT_PERIODIC_SUCCESS);
    }

    /**
     * Output 9 — Laporan Aktivitas Reservasi per Divisi
     * GET /v1/reports/division-activity?format=json|pdf|excel&date_from=&date_to=
     */
    public function divisionActivity(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->orderBy('start_time');

        if ($request->filled('date_from')) {
            $query->where('start_time', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('start_time', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $reservations = $query->get();

        $byDivision = $reservations->groupBy(fn ($r) => $r->user?->division_id ?? '__no_division__')
            ->map(function ($items) {
                $first = $items->first();
                $division = $first->user?->division;

                return [
                    'division_id' => $division?->id ?? null,
                    'division_name' => $division?->name ?? 'Admin / Tanpa Divisi',
                    'division_code' => $division?->code ?? '-',
                    'total' => $items->count(),
                    ReservationStatus::Approved->value => $items->where('status', ReservationStatus::Approved->value)->count(),
                    ReservationStatus::Completed->value => $items->where('status', ReservationStatus::Completed->value)->count(),
                    ReservationStatus::Rejected->value => $items->where('status', ReservationStatus::Rejected->value)->count(),
                    ReservationStatus::Cancelled->value => $items->where('status', ReservationStatus::Cancelled->value)->count(),
                    ReservationStatus::Pending->value => $items->where('status', ReservationStatus::Pending->value)->count(),
                    'visitors' => $items->sum('visitor_count'),
                ];
            })->values()->sortByDesc('total')->values();

        $summary = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'total_reservations' => $reservations->count(),
            'total_visitors' => $reservations->sum('visitor_count'),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.division-activity', compact('reservations', 'byDivision', 'summary'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-aktivitas-divisi-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new DivisionActivityReportExport($reservations, $byDivision, $summary),
                'laporan-aktivitas-divisi-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'by_division' => $byDivision,
            'reservations' => $reservations,
        ], ApiMessages::REPORT_DIVISION_ACTIVITY_SUCCESS);
    }

    /**
     * Output 10 — Laporan Maintenance & Kerusakan Ruangan
     * GET /v1/reports/maintenance?format=json|pdf|excel&date_from=&date_to=&room_id=
     */
    public function maintenance(Request $request): mixed
    {
        $roomFilters = $this->normalizeFilterValues($request->input('room_id'));

        $validator = Validator::make([
            ...$request->all(),
            'room_id' => $roomFilters,
        ], [
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'room_id' => 'nullable|array',
            'room_id.*' => 'string|exists:m_rooms,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $roomQuery = Room::query()
            ->whereNull('deleted_at')
            ->orderBy('floor')
            ->orderBy('name');

        if ($roomFilters !== []) {
            $roomQuery->whereIn('id', $roomFilters);
        }

        $allRooms = $roomQuery->get();

        $complaintQuery = RoomComplaint::query()
            ->whereNull('deleted_at');

        if ($request->filled('date_from')) {
            $complaintQuery->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $complaintQuery->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $complaints = $complaintQuery
            ->when($roomFilters !== [], fn ($q) => $q->whereIn('room_id', $roomFilters))
            ->get()
            ->groupBy('room_id');

        $rooms = $allRooms->map(function ($room) use ($complaints) {
            $roomComplaints = $complaints->get($room->id, collect());

            return [
                'id' => $room->id,
                'name' => $room->name,
                'floor' => $room->floor,
                'capacity' => $room->capacity,
                'is_maintenance' => (bool) $room->is_maintenance,
                'total_complaints' => $roomComplaints->count(),
                'open' => $roomComplaints->where('status', 'open')->count(),
                'in_progress' => $roomComplaints->where('status', 'in_progress')->count(),
                'resolved' => $roomComplaints->where('status', 'resolved')->count(),
                'rejected' => $roomComplaints->where('status', 'rejected')->count(),
            ];
        });

        $allComplaints = $complaints->flatten(1);

        $summary = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'total_rooms' => $allRooms->count(),
            'under_maintenance' => $allRooms->where('is_maintenance', true)->count(),
            'total_complaints' => $allComplaints->count(),
            'open_complaints' => $allComplaints->where('status', 'open')->count(),
            'resolved_complaints' => $allComplaints->where('status', 'resolved')->count(),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.maintenance', compact('rooms', 'summary'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-maintenance-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new MaintenanceReportExport($rooms, $summary),
                'laporan-maintenance-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'rooms' => $rooms,
        ], ApiMessages::REPORT_MAINTENANCE_SUCCESS);
    }

    /**
     * Output 11 — Rekapitulasi Pemakaian Ruangan per Divisi
     * GET /v1/reports/division-usage?format=json|pdf|excel&date_from=&date_to=
     */
    public function divisionUsage(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Reservation::query()
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id,division_id', 'user.division:id,name,code'])
            ->whereNull('deleted_at')
            ->whereIn('status', [ReservationStatus::Approved->value, ReservationStatus::Completed->value])
            ->orderBy('start_time');

        if ($request->filled('date_from')) {
            $query->where('start_time', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('start_time', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $reservations = $query->get();

        $byDivision = $reservations->groupBy(fn ($r) => $r->user?->division_id ?? '__no_division__')
            ->map(function ($items) {
                $first = $items->first();
                $division = $first->user?->division;

                $totalMinutes = $items->sum(fn ($r) => $r->start_time->diffInMinutes($r->end_time));
                $totalHours = round($totalMinutes / 60, 1);
                $count = $items->count();

                $roomBreakdown = $items->groupBy('room_id')->map(function ($ri) {
                    $r = $ri->first();
                    $mins = $ri->sum(fn ($rv) => $rv->start_time->diffInMinutes($rv->end_time));

                    return [
                        'room_name' => $r->room?->name ?? '-',
                        'floor' => $r->room?->floor ?? '-',
                        'count' => $ri->count(),
                        'hours' => round($mins / 60, 1),
                        'visitors' => $ri->sum('visitor_count'),
                    ];
                })->values()->sortByDesc('hours')->values()->toArray();

                return [
                    'division_id' => $division?->id ?? null,
                    'division_name' => $division?->name ?? 'Admin / Tanpa Divisi',
                    'division_code' => $division?->code ?? '-',
                    'reservation_count' => $count,
                    'total_hours' => $totalHours,
                    'avg_hours' => $count > 0 ? round($totalHours / $count, 1) : 0,
                    'total_visitors' => $items->sum('visitor_count'),
                    'rooms_used' => collect($roomBreakdown)->pluck('room_name')->unique()->values()->toArray(),
                    'room_breakdown' => $roomBreakdown,
                ];
            })->values()->sortByDesc('total_hours')->values();

        $summary = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'total_reservations' => $reservations->count(),
            'total_hours' => round($reservations->sum(fn ($r) => $r->start_time->diffInMinutes($r->end_time)) / 60, 1),
            'total_visitors' => $reservations->sum('visitor_count'),
        ];

        $format = $request->input('format', 'json');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.division-usage', compact('byDivision', 'summary'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-pemakaian-divisi-'.now()->format('Ymd').'.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new DivisionUsageReportExport($byDivision, $summary),
                'laporan-pemakaian-divisi-'.now()->format('Ymd').'.xlsx'
            );
        }

        return ApiResponse::success([
            'summary' => $summary,
            'by_division' => $byDivision,
        ], ApiMessages::REPORT_DIVISION_USAGE_SUCCESS);
    }
}
