<?php

namespace App\Http\Responses;

use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Success response format
     *
     * @param mixed $data The response data
     * @param string $message User-facing message in Indonesian
     * @param int $statusCode HTTP status code
     * @param array $metadata Optional metadata (pagination, etc)
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        string $message = ApiMessages::SUCCESS_GENERIC,
        int $statusCode = 200,
        array $metadata = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($metadata)) {
            $response['metadata'] = $metadata;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response format
     *
     * @param string $errorCode Machine-readable error code
     * @param string $message User-facing message in Indonesian
     * @param int $statusCode HTTP status code
     * @param array $errors Validation errors or additional error details
     * @return JsonResponse
     */
    public static function error(
        string $errorCode,
        string $message = 'Terjadi kesalahan',
        int $statusCode = 400,
        array $errors = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'error_code' => $errorCode,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Paginated success response
     *
        * @param LengthAwarePaginator $paginated
     * @param string $message User-facing message in Indonesian
     * @return JsonResponse
     */
        public static function paginated(LengthAwarePaginator $paginated, string $message = 'Data berhasil diambil'): JsonResponse
    {
        return self::success(
            $paginated->items(),
            $message,
            200,
            [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ]
        );
    }

    /**
     * Validation error response
     *
     * @param array $errors Laravel validation errors
     * @return JsonResponse
     */
    public static function validationError(array $errors): JsonResponse
    {
        return self::error(
            ApiErrorCodes::VALIDATION_ERROR,
            ApiMessages::SUCCESS_VALIDATION,
            422,
            $errors
        );
    }

    /**
     * Unauthorized response
     *
     * @return JsonResponse
     */
    public static function unauthorized(): JsonResponse
    {
        return self::error(
            ApiErrorCodes::UNAUTHORIZED,
            ApiMessages::UNAUTHORIZED,
            401
        );
    }

    /**
     * Forbidden response
     *
     * @return JsonResponse
     */
    public static function forbidden(): JsonResponse
    {
        return self::error(
            ApiErrorCodes::FORBIDDEN,
            ApiMessages::FORBIDDEN,
            403
        );
    }

    /**
     * Not found response
     *
     * @param string $message User-facing message in Indonesian
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource tidak ditemukan'): JsonResponse
    {
        return self::error(
            ApiErrorCodes::NOT_FOUND,
            $message,
            404
        );
    }
}
