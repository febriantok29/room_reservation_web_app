<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group System
 *
 * Endpoint kesehatan/ping untuk memverifikasi koneksi aplikasi ke server.
 */
class PingController extends Controller
{
    /**
     * Ping
     *
     * Mengecek koneksi API dan status database. Publik (tanpa autentikasi),
     * dipakai aplikasi mobile untuk "Test Koneksi" pada pengaturan server.
     *
     * @unauthenticated
     *
     * @response 200 scenario="Sukses" {
     *   "success": true,
     *   "message": "pong",
     *   "data": {
     *     "server_time": "2026-08-20T12:00:00Z",
     *     "server_timezone": "UTC",
     *     "app_version": "1.0.0",
     *     "db_status": "ok"
     *   }
     * }
     */
    public function ping(): JsonResponse
    {
        $dbStatus = 'ok';

        try {
            DB::selectOne('select 1');
        } catch (\Throwable) {
            $dbStatus = 'error';
        }

        return ApiResponse::success(
            [
                'server_time' => now()->toIso8601String(),
                'server_timezone' => config('app.timezone', 'UTC'),
                'app_version' => '1.0.0',
                'db_status' => $dbStatus,
            ],
            'pong'
        );
    }
}
