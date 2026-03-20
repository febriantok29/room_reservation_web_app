<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomComplaint;
use App\Models\User;
use App\Support\ApiMessages;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComplaintReportExport;
use App\Exports\UsageReportExport;
use App\Exports\UserActivityReportExport;
use App\Exports\ScheduleHistoryReportExport;
use App\Exports\PeriodicReportExport;

class ReportController extends Controller
{
    /**
     * Output 3 — Complaint & Facility Damage Report
     * GET /v1/reports/complaints?format=json|pdf|excel&date_from=&date_to=&status=&room_id=
     */
    public function complaints(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'format'    => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'status'    => 'nullable|in:open,in_progress,resolved,rejected',
            'room_id'   => 'nullable|string|exists:m_rooms,id',
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
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        $complaints = $query->get();

        $summary = [
            'total'       => $complaints->count(),
            'open'        => $complaints->where('status', 'open')->count(),
            'in_progress' => $complaints->where('status', 'in_progress')->count(),
            'resolved'    => $complaints->where('status', 'resolved')->count(),
            'rejected'    => $complaints->where('status', 'rejected')->count(),
        ];

        $format = $request->input('format', 'json');

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

        return ApiResponse::success([
            'summary'    => $summary,
            'complaints' => $complaints,
        ], ApiMessages::REPORT_COMPLAINT_SUCCESS);
    }

    /**
     * Output 5 — Rekapitulasi Penggunaan Ruangan
     * GET /v1/reports/usage?format=json|pdf|excel&date_from=&date_to=&room_id=
     */
    public function usage(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'format'    => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'room_id'   => 'nullable|string|exists:m_rooms,id',
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

        $query = Reservation::query()
            ->with(['room:id,name,floor,capacity', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->whereIn('status', ['approved', 'completed'])
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to);

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        $reservations = $query->orderBy('start_time')->get();

        // Aggregate per room
        $byRoom = $reservations->groupBy('room_id')->map(function ($items) {
            $first = $items->first();
            $totalMinutes = $items->sum(fn($r) => $r->start_time->diffInMinutes($r->end_time));
            return [
                'room_id'       => $first->room_id,
                'room_name'     => $first->room?->name ?? '-',
                'floor'         => $first->room?->floor ?? null,
                'reserved_count' => $items->count(),
                'total_hours'   => round($totalMinutes / 60, 1),
                'total_visitors' => $items->sum('visitor_count'),
            ];
        })->values();

        $summary = [
            'date_from'         => $from->toDateString(),
            'date_to'           => $to->toDateString(),
            'total_reservations' => $reservations->count(),
            'total_rooms_used'  => $byRoom->count(),
            'total_visitors'    => $reservations->sum('visitor_count'),
        ];

        $format = $request->input('format', 'json');

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

        return ApiResponse::success([
            'summary'      => $summary,
            'by_room'      => $byRoom,
            'reservations' => $reservations,
        ], ApiMessages::REPORT_USAGE_SUCCESS);
    }

    /**
     * Output 6 — Laporan Aktivitas Reservasi per Pengguna
     * GET /v1/reports/user-activity?format=json|pdf|excel&date_from=&date_to=&user_id=
     */
    public function userActivity(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'format'    => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'user_id'   => 'nullable|string|exists:s_users,id',
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
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time');

        // Non-admins can only see their own
        if (!$actor->canApprove()) {
            $query->where('user_id', $actor->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $reservations = $query->get();

        // Aggregate per user
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
            'date_from'         => $from->toDateString(),
            'date_to'           => $to->toDateString(),
            'total_reservations' => $reservations->count(),
            'total_users'       => $byUser->count(),
        ];

        $format = $request->input('format', 'json');

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

        return ApiResponse::success([
            'summary'      => $summary,
            'by_user'      => $byUser,
            'reservations' => $reservations,
        ], ApiMessages::REPORT_USER_ACTIVITY_SUCCESS);
    }

    /**
     * Output 7 — Laporan Jadwal & Histori Reservasi
     * GET /v1/reports/schedule-history?format=json|pdf|excel&date_from=&date_to=&status=&room_id=
     */
    public function scheduleHistory(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'format'    => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'status'    => 'nullable|in:pending,approved,rejected,completed,cancelled',
            'room_id'   => 'nullable|string|exists:m_rooms,id',
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
            ->with(['room:id,name,floor', 'user:id,first_name,last_name,employee_id'])
            ->whereNull('deleted_at')
            ->where('start_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time');

        if (!$actor->canApprove()) {
            $query->where('user_id', $actor->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
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

        $format = $request->input('format', 'json');

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

        return ApiResponse::success([
            'summary'      => $summary,
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
            'year'   => 'nullable|integer|min:2000|max:2100',
            'month'  => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $period = $request->input('period', 'monthly');
        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);

        if ($period === 'daily') {
            $from = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $to   = $from->copy()->endOfMonth()->endOfDay();
            $groupBy = fn($r) => $r->start_time->toDateString();
            $labelKey = 'date';
        } elseif ($period === 'weekly') {
            $from = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $to   = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $groupBy = fn($r) => 'Week ' . $r->start_time->isoWeek;
            $labelKey = 'week';
        } else {
            // monthly — show all months of the year
            $from = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $to   = Carbon::createFromDate($year, 12, 31)->endOfDay();
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
                $labelKey    => $key,
                'total'      => $items->count(),
                'approved'   => $items->where('status', 'approved')->count(),
                'completed'  => $items->where('status', 'completed')->count(),
                'rejected'   => $items->where('status', 'rejected')->count(),
                'cancelled'  => $items->where('status', 'cancelled')->count(),
                'pending'    => $items->where('status', 'pending')->count(),
                'visitors'   => $items->sum('visitor_count'),
            ];
        })->values();

        $summary = [
            'period'     => $period,
            'year'       => $year,
            'month'      => $period === 'daily' ? $month : null,
            'total'      => $reservations->count(),
            'completed'  => $reservations->where('status', 'completed')->count(),
            'approved'   => $reservations->where('status', 'approved')->count(),
            'rejected'   => $reservations->where('status', 'rejected')->count(),
            'cancelled'  => $reservations->where('status', 'cancelled')->count(),
        ];

        $format = $request->input('format', 'json');

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

        return ApiResponse::success([
            'summary' => $summary,
            'data'    => $grouped,
        ], ApiMessages::REPORT_PERIODIC_SUCCESS);
    }
}
