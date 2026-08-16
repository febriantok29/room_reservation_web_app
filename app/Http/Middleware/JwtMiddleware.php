<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Http\Responses\ApiResponse;
use App\Services\JwtService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     *
        * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = null;

        // 1. Try JWT Authentication First
        $authHeader = $request->header('Authorization');

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $verification = $this->jwtService->verifyToken($token);

            if ($verification['success']) {
                $decoded = $verification['data'];

                // Check if it's an access token
                if ($decoded->type !== 'access') {
                    return ApiResponse::error(
                        'INVALID_TOKEN_TYPE',
                        'Token yang dikirim bukan access token',
                        401
                    );
                }

                $user = User::find($decoded->sub);
            }
        }

        // 2. ALTERNATIVE AUTH: If JWT fails or is missing, check user_id
        if (!$user) {
            // Check 'X-User-Id' header first, then fallback to 'user_id' in request input
            $userId = $request->header('X-User-Id') ?? $request->input('user_id');
            
            if ($userId) {
                $user = User::find($userId);
            }
        }

        // 3. Final Check: If no user found from either method or user inactive
        if (!$user || !$user->is_active) {
            return ApiResponse::unauthorized();
        }

        // Check admin authorization if specified via middleware args
        if (!empty($roles)) {
            $requiresAdmin = in_array('admin', $roles, true);

            if ($requiresAdmin && !$user->isAdmin()) {
                return ApiResponse::forbidden();
            }
        }

        // Force password change: block everything except the change-password endpoint
        if ($user->must_change_password && ! $request->is('api/v1/auth/change-password')) {
            return ApiResponse::error(
                ApiErrorCodes::PASSWORD_CHANGE_REQUIRED,
                ApiMessages::AUTH_PASSWORD_CHANGE_REQUIRED,
                403
            );
        }

        // Inject user into request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
