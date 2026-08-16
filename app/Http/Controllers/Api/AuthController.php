<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\FcmToken;
use App\Models\User;
use App\Services\JwtService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @group Auth
 *
 * Login, refresh token, dan manajemen sesi. Token JWT kustom (bukan Sanctum) — access token berlaku
 * 15 menit, refresh token 7 hari.
 */
class AuthController extends Controller
{
    private JwtService $jwtService;

    private function canBypass(string $password): bool
    {
        return config('app.debug') && config('auth.bypass_password') && hash_equals(config('auth.bypass_password'), $password);
    }

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Login
     *
     * Login menggunakan email atau employee_id, mengembalikan access_token dan refresh_token.
     *
     * @unauthenticated
     *
     * @bodyParam login string Email atau employee_id (alternatif dari email/employee_id terpisah). Example: budi@haleyorapower.co.id
     * @bodyParam email string Email karyawan. Example: budi@haleyorapower.co.id
     * @bodyParam employee_id string Kode divisi + nomor pegawai. Example: IT-001
     * @bodyParam password string required Kata sandi. Example: password123
     * @bodyParam fcm_token string Token FCM device untuk push notification. Example: fcm_device_token_xyz
     * @bodyParam is_debug boolean Jika true, TTL token bisa dikustomisasi lewat access_token_ttl/refresh_token_ttl. Example: false
     * @bodyParam access_token_ttl integer TTL access token dalam detik (hanya berlaku jika is_debug true). Example: 900
     * @bodyParam refresh_token_ttl integer TTL refresh token dalam detik (hanya berlaku jika is_debug true). Example: 604800
     *
     * @response 200 scenario="Login berhasil" {
     *   "success": true,
     *   "message": "Login berhasil",
     *   "data": {
     *     "access_token": "eyJhbGciOiJIUzI1NiIs...",
     *     "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
     *     "user": {
     *       "id": "USR-001",
     *       "name": "Budi Santoso",
     *       "email": "budi@haleyorapower.co.id",
     *       "employee_id": "IT-001",
     *       "division_id": "DIV-01",
     *       "division": {"id": "DIV-01", "name": "Information Technology", "code": "IT"},
     *       "is_admin": false,
     *       "is_active": true
     *     }
     *   }
     * }
     * @response 401 scenario="Email/employee_id atau password salah" {
     *   "success": false,
     *   "error_code": "INVALID_CREDENTIALS",
     *   "message": "Email/No. Induk Karyawan atau password salah"
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required_without:email,employee_id|string|max:100',
            'email' => 'nullable|email|max:100',
            'employee_id' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'fcm_token' => 'nullable|string|max:512',
            'is_debug' => 'nullable|boolean',
            'access_token_ttl' => 'nullable|integer|min:1',
            'refresh_token_ttl' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $login = trim((string) ($request->input('login') ?? $request->input('email') ?? $request->input('employee_id')));

        // Find user by email or employee_id
        $user = User::where('email', $login)
            ->orWhere('employee_id', $login)
            ->first();

        if (! $user || (! $this->canBypass($request->password) && ! Hash::check($request->password, $user->password))) {
            return ApiResponse::error(
                ApiErrorCodes::INVALID_CREDENTIALS,
                ApiMessages::AUTH_INVALID_CREDENTIALS,
                401
            );
        }

        // Check if user is active
        if ($user->is_active === false) {
            return ApiResponse::error(
                ApiErrorCodes::USER_INACTIVE,
                ApiMessages::AUTH_USER_INACTIVE,
                403
            );
        }

        // Debug TTL overrides only work when APP_DEBUG is on
        $isDebug = config('app.debug') && $request->boolean('is_debug', false);
        $accessTokenTtl = $request->input('access_token_ttl');
        $refreshTokenTtl = $request->input('refresh_token_ttl');

        $tokens = $this->jwtService->issueTokens(
            $user,
            $isDebug,
            $accessTokenTtl,
            $refreshTokenTtl
        );

        $tokens['user'] = $user->toAuthArray();

        // Register FCM token for this device if provided
        if ($fcmToken = $request->input('fcm_token')) {
            FcmToken::register($user->id, $fcmToken);
        }

        return ApiResponse::success(
            $tokens,
            ApiMessages::AUTH_LOGIN_SUCCESS,
            200
        );
    }

    /**
     * Refresh token
     *
     * Menukar refresh_token yang masih valid dengan access_token baru.
     *
     * @unauthenticated
     *
     * @bodyParam refresh_token string required Refresh token yang didapat saat login. Example: eyJhbGciOiJIUzI1NiIs...
     * @bodyParam is_debug boolean Jika true, TTL access token bisa dikustomisasi lewat access_token_ttl. Example: false
     * @bodyParam access_token_ttl integer TTL access token dalam detik (hanya berlaku jika is_debug true). Example: 900
     *
     * @response 200 scenario="Refresh berhasil" {
     *   "success": true,
     *   "message": "Token refresh berhasil",
     *   "data": {
     *     "access_token": "eyJhbGciOiJIUzI1NiIs...",
     *     "token_type": "Bearer",
     *     "expires_in": 900,
     *     "is_debug": false
     *   }
     * }
     * @response 401 scenario="Refresh token tidak valid/kedaluwarsa" {
     *   "success": false,
     *   "error_code": "UNAUTHORIZED",
     *   "message": "Token tidak valid atau kadaluarsa"
     * }
     */
    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
            'is_debug' => 'nullable|boolean',
            'access_token_ttl' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $isDebug = config('app.debug') && $request->boolean('is_debug', false);
        $accessTokenTtl = $request->input('access_token_ttl');

        $tokens = $this->jwtService->refreshAccessToken(
            $request->input('refresh_token'),
            $isDebug,
            $accessTokenTtl
        );

        if (! $tokens) {
            return ApiResponse::unauthorized();
        }

        return ApiResponse::success(
            $tokens,
            ApiMessages::AUTH_REFRESH_SUCCESS,
            200
        );
    }

    /**
     * Logout
     *
     * Menghapus FCM token perangkat ini (opsional) agar tidak lagi menerima push notification.
     *
     * @bodyParam fcm_token string Token FCM device yang ingin dicabut. Example: fcm_device_token_xyz
     *
     * @response 200 scenario="Logout berhasil" {
     *   "success": true,
     *   "message": "Logout berhasil. Mohon hapus token dari perangkat Anda",
     *   "data": null
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'nullable|string|max:512',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        if ($fcmToken = $request->input('fcm_token')) {
            FcmToken::revoke($fcmToken);
        }

        return ApiResponse::success(
            null,
            ApiMessages::AUTH_LOGOUT_SUCCESS,
            200
        );
    }

    /**
     * Profil saya
     *
     * Mengambil data karyawan yang sedang login berdasarkan access token.
     *
     * @response 200 scenario="Sukses" {
     *   "success": true,
     *   "message": "Data pengguna berhasil diambil",
     *   "data": {
     *     "id": "USR-001",
     *     "name": "Budi Santoso",
     *     "email": "budi@haleyorapower.co.id",
     *     "employee_id": "IT-001",
     *     "is_admin": false,
     *     "is_active": true
     *   }
     * }
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        return ApiResponse::success(
            $user->toAuthArray(),
            ApiMessages::AUTH_ME_SUCCESS,
            200
        );
    }

    /**
     * Update FCM token
     *
     * Daftarkan atau perbarui token FCM device untuk user yang sedang login. Panggil setiap kali
     * Firebase SDK memberi token baru (onTokenRefresh).
     *
     * @bodyParam fcm_token string required Token FCM device. Example: fcm_device_token_xyz
     *
     * @response 200 scenario="Sukses" {
     *   "success": true,
     *   "message": "FCM token updated successfully.",
     *   "data": null
     * }
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|max:512',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        FcmToken::register($request->user()->id, $request->input('fcm_token'));

        return ApiResponse::success(null, ApiMessages::AUTH_FCM_TOKEN_UPDATED, 200);
    }
}
