<?php

use App\Http\Middleware\EnsureAdminMiddleware;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Responses\ApiResponse;
use App\Services\ErrorLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => JwtMiddleware::class,
            'admin' => EnsureAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            // Let expected client errors keep Laravel's default handling (not logged as internal errors).
            if ($e instanceof ValidationException || $e instanceof AuthenticationException || $e instanceof AuthorizationException) {
                return null;
            }

            if ($e instanceof HttpException && $e->getStatusCode() < 500) {
                return null;
            }

            $service = app(ErrorLogService::class);

            $code = $service->log(
                $e,
                $request->user()?->id,
                $request->fullUrl(),
                $request->method(),
                $request->isMethod('GET') ? [] : $request->all()
            );

            $message = 'Terjadi kesalahan pada server. Silakan hubungi admin dengan kode ' . $code . '.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponse::error($code, $message, 500);
            }

            return response()->view('errors.server-error', [
                'error_code' => $code,
                'message' => $message,
            ], 500);
        });
    })->create();