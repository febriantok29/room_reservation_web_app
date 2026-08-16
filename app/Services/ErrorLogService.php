<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorLogService
{
    /**
     * Crockford Base32 alphabet (no I, L, O, U to avoid ambiguity) — 32 symbols.
     */
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    private const CODE_LENGTH = 6;

    private const SENSITIVE_KEYS = [
        'password',
        'current_password',
        'new_password',
        'new_password_confirmation',
        'password_confirmation',
        'token',
        'access_token',
        'refresh_token',
        'fcm_token',
        'api_key',
        'secret',
        'authorization',
    ];

    /**
     * Log an uncaught exception and return a short user-facing error code.
     */
    public function log(Throwable $e, ?string $userId = null, ?string $endpoint = null, ?string $method = null, ?array $requestBody = null): string
    {
        $code = $this->generateUniqueCode();

        try {
            ErrorLog::query()->create([
                'error_code' => $code,
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
                'user_id' => $userId,
                'endpoint' => $endpoint ?? '',
                'http_method' => $method ?? '',
                'request_body' => $requestBody === null ? null : $this->sanitize($requestBody),
                'created_at' => now(),
            ]);
        } catch (Throwable $fallback) {
            Log::error('Gagal menyimpan error log', [
                'error_code' => $code,
                'exception' => $e->getMessage(),
                'log_exception' => $fallback->getMessage(),
            ]);
        }

        return $code;
    }

    private function generateUniqueCode(): string
    {
        $alphabetLength = strlen(self::ALPHABET);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
            }

            if (! ErrorLog::query()->where('error_code', $code)->exists()) {
                return $code;
            }
        }

        // Extremely unlikely path: force a fresh value with a tiebreaker.
        return $code . random_int(0, 9);
    }

    /**
     * Recursively strip sensitive keys before persisting request data.
     */
    private function sanitize(array $body): string
    {
        $clean = $this->stripSensitive($body);
        $encoded = json_encode($clean);

        return $encoded === false ? '' : substr($encoded, 0, 65535);
    }

    private function stripSensitive(mixed $value): mixed
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                if (is_string($key) && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
                    $result[$key] = '[REDACTED]';
                    continue;
                }

                $result[$key] = $this->stripSensitive($item);
            }

            return $result;
        }

        return $value;
    }
}
