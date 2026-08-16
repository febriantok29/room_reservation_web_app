<?php

use App\Models\ErrorLog;
use App\Models\User;
use App\Services\ErrorLogService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

it('logs an exception and returns a short unique code', function () {
    $service = new ErrorLogService;
    $exception = new RuntimeException('database went away');

    $code = $service->log($exception, null, '/api/v1/rooms', 'GET', []);

    expect(strlen($code))->toBe(6)
        ->and(ctype_alnum($code))->toBeTrue();

    $log = ErrorLog::query()->where('error_code', $code)->first();
    expect($log)->not->toBeNull()
        ->and($log->message)->toBe('database went away')
        ->and($log->endpoint)->toBe('/api/v1/rooms')
        ->and($log->http_method)->toBe('GET');
});

it('sanitizes sensitive data from request body', function () {
    $service = new ErrorLogService;

    $code = $service->log(
        new RuntimeException('boom'),
        null,
        '/api/v1/auth/login',
        'POST',
        ['email' => 'budi@example.com', 'password' => 'secret123', 'token' => 'abc']
    );

    $log = ErrorLog::query()->where('error_code', $code)->first();
    $body = json_decode($log->request_body, true);

    expect($body['email'])->toBe('budi@example.com')
        ->and($body['password'])->toBe('[REDACTED]')
        ->and($body['token'])->toBe('[REDACTED]');
});

it('returns a safe JSON error with code on API 500', function () {
    Route::get('/api/v1/auth/__trigger-error', fn () => throw new RuntimeException('database went away'));

    $response = $this->getJson('/api/v1/auth/__trigger-error');

    $response->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertSeeText('hubungi admin dengan kode');

    $code = $response->json('error_code');
    expect(ErrorLog::query()->where('error_code', $code)->exists())->toBeTrue();
});

it('returns a safe web error page with code on unexpected exception', function () {
    Route::middleware('web')->prefix('__test')->group(function () {
        Route::get('/boom', fn () => throw new RuntimeException('database went away'));
    });

    $response = $this->get('/__test/boom');

    $response->assertStatus(500);
    // Page must not leak exception details
    $response->assertDontSee('database went away');
    $response->assertSeeText('hubungi admin dengan kode');
});

it('does not log expected 404 errors', function () {
    $response = $this->getJson('/api/v1/nonexistent/endpoint');

    $response->assertStatus(404);
    expect(ErrorLog::query()->count())->toBe(0);
});