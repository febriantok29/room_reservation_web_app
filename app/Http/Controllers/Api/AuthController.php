<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\JwtService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
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
            'login' => 'required_without:email,employee_id|string|max:100',
            'email' => 'nullable|email|max:100',
            'employee_id' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
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

        if (!$user || !Hash::check($request->password, $user->password)) {
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
            ApiMessages::AUTH_LOGIN_SUCCESS,
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
            ApiMessages::AUTH_REFRESH_SUCCESS,
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
            ApiMessages::AUTH_LOGOUT_SUCCESS,
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
                'name' => $user->full_name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'is_admin' => $user->is_admin,
                'is_active' => $user->is_active,
            ],
            ApiMessages::AUTH_ME_SUCCESS,
            200
        );
    }
}
