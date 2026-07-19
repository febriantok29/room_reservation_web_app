<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\EnsuresAdminAccess;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use EnsuresAdminAccess;

    /**
     * List active non-admin users.
     * Used by admin when creating a reservation on behalf of another user.
     */
    public function index(Request $request): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = User::query()
            ->where('is_admin', false)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('employee_id', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('per_page')) {
            return ApiResponse::paginated(
                $query->paginate((int) $request->input('per_page')),
                ApiMessages::USER_LIST_SUCCESS
            );
        }

        return ApiResponse::success($query->get(), ApiMessages::USER_LIST_SUCCESS);
    }

}
