<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminErrorLogController extends Controller
{
    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $query = ErrorLog::query()->orderByDesc('created_at');

        if ($request->filled('error_code')) {
            $query->where('error_code', 'like', '%' . $request->input('error_code') . '%');
        }

        if ($request->filled('endpoint')) {
            $query->where('endpoint', 'like', '%' . $request->input('endpoint') . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        return view('admin.tools.error-logs', [
            'logs' => $query->paginate(15)->withQueryString(),
            'filterErrorCode' => $request->input('error_code', ''),
            'filterEndpoint' => $request->input('endpoint', ''),
            'filterUserId' => $request->input('user_id', ''),
            'filterDate' => $request->input('date', ''),
        ]);
    }

    public function show(Request $request, ErrorLog $log): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.tools.error-log-detail', ['log' => $log]);
    }
}