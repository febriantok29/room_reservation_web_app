<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Division;
use App\Services\DivisionIdGenerator;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DivisionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Division::query()->whereNull('deleted_at')->orderBy('id');

        if ($request->filled('q')) {
            $kw = trim((string) $request->input('q'));
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('code', 'like', "%{$kw}%");
            });
        }

        return ApiResponse::success($query->get(), ApiMessages::DIVISION_LIST_SUCCESS);
    }

    public function show(string $id): JsonResponse
    {
        $division = Division::query()->whereNull('deleted_at')->where('id', $id)->first();

        if (!$division) {
            return ApiResponse::notFound(ApiMessages::DIVISION_NOT_FOUND);
        }

        return ApiResponse::success($division, ApiMessages::DIVISION_DETAIL_SUCCESS);
    }

    public function store(Request $request): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $name = Str::of((string) $request->input('name'))->squish()->toString();
        $code = strtoupper(trim((string) $request->input('code')));

        if (Division::query()->whereNull('deleted_at')->where('code', $code)->exists()) {
            return ApiResponse::validationError(['code' => ['Kode divisi sudah digunakan.']]);
        }
        if (Division::query()->whereNull('deleted_at')->where('name', $name)->exists()) {
            return ApiResponse::validationError(['name' => ['Nama divisi sudah digunakan.']]);
        }

        $division = Division::query()->create([
            'id'          => DivisionIdGenerator::generate(),
            'name'        => $name,
            'code'        => $code,
            'description' => $request->input('description'),
            'created_by'  => $request->user()?->id,
        ]);

        return ApiResponse::created($division, ApiMessages::DIVISION_CREATED_SUCCESS);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $division = Division::query()->whereNull('deleted_at')->where('id', $id)->first();

        if (!$division) {
            return ApiResponse::notFound(ApiMessages::DIVISION_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'nullable|string|max:100',
            'code'        => 'nullable|string|max:10',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        if ($request->filled('name')) {
            $name = Str::of((string) $request->input('name'))->squish()->toString();
            if (Division::query()->whereNull('deleted_at')->where('name', $name)->where('id', '!=', $id)->exists()) {
                return ApiResponse::validationError(['name' => ['Nama divisi sudah digunakan.']]);
            }
            $division->name = $name;
        }

        if ($request->filled('code')) {
            $code = strtoupper(trim((string) $request->input('code')));
            if (Division::query()->whereNull('deleted_at')->where('code', $code)->where('id', '!=', $id)->exists()) {
                return ApiResponse::validationError(['code' => ['Kode divisi sudah digunakan.']]);
            }
            $division->code = $code;
        }

        if ($request->has('description')) {
            $division->description = $request->input('description');
        }

        $division->updated_by = $request->user()?->id;
        $division->save();

        return ApiResponse::success($division, ApiMessages::DIVISION_UPDATED_SUCCESS);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $division = Division::query()->whereNull('deleted_at')->where('id', $id)->first();

        if (!$division) {
            return ApiResponse::notFound(ApiMessages::DIVISION_NOT_FOUND);
        }

        $division->deleted_by = $request->user()?->id;
        $division->save();
        $division->delete();

        return ApiResponse::success(null, ApiMessages::DIVISION_DELETED_SUCCESS);
    }
}
