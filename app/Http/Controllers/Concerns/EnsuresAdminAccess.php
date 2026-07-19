<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait EnsuresAdminAccess
{
    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return ApiResponse::forbidden();
        }

        return null;
    }
}
