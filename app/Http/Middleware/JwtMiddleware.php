<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Http\Responses\ApiResponse;
use App\Services\JwtService;
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
        // Get token from Authorization header
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return ApiResponse::unauthorized();
        }

        $token = substr($authHeader, 7);

        // Verify token
        $verification = $this->jwtService->verifyToken($token);

        if (!$verification['success']) {
            return ApiResponse::unauthorized();
        }

        $decoded = $verification['data'];

        // Check if it's an access token
        if ($decoded->type !== 'access') {
            return ApiResponse::error(
                'INVALID_TOKEN_TYPE',
                'Token yang dikirim bukan access token',
                401
            );
        }

        // Get user from database
        $user = User::find($decoded->sub);

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

        // Inject user into request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
