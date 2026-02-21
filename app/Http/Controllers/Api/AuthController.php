<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Login user and return JWT tokens
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'is_debug' => 'nullable|boolean',
            'access_token_ttl' => 'nullable|integer|min:1',
            'refresh_token_ttl' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error(
                'INVALID_CREDENTIALS',
                'Email atau password salah',
                401
            );
        }

        // Check if user is active
        if ($user->is_active === false) {
            return ApiResponse::error(
                'USER_INACTIVE',
                'Akun Anda telah dinonaktifkan',
                403
            );
        }

        // Issue tokens with optional debug mode
        $isDebug = $request->boolean('is_debug', false);
        $accessTokenTtl = $request->input('access_token_ttl');
        $refreshTokenTtl = $request->input('refresh_token_ttl');

        $tokens = $this->jwtService->issueTokens(
            $user,
            $isDebug,
            $accessTokenTtl,
            $refreshTokenTtl
        );

        return ApiResponse::success(
            $tokens,
            'Login berhasil',
            200
        );
    }

    /**
     * Refresh access token using refresh token
     *
     * @param Request $request
     * @return JsonResponse
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

        $isDebug = $request->boolean('is_debug', false);
        $accessTokenTtl = $request->input('access_token_ttl');

        $tokens = $this->jwtService->refreshAccessToken(
            $request->input('refresh_token'),
            $isDebug,
            $accessTokenTtl
        );

        if (!$tokens) {
            return ApiResponse::unauthorized();
        }

        return ApiResponse::success(
            $tokens,
            'Token refresh berhasil',
            200
        );
    }

    /**
     * Logout user (client-side should delete tokens)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // In stateless JWT implementation, logout is handled client-side by deleting tokens
        // This endpoint is just for confirmation/logging purposes

        return ApiResponse::success(
            null,
            'Logout berhasil. Mohon hapus token dari perangkat Anda',
            200
        );
    }

    /**
     * Get current authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized();
        }

        return ApiResponse::success(
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
            'Data pengguna berhasil diambil',
            200
        );
    }
}
