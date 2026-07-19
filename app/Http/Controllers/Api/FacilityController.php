<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\EnsuresAdminAccess;
use App\Http\Responses\ApiResponse;
use App\Models\Facility;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class FacilityController extends Controller
{
    use EnsuresAdminAccess;

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Facility::query()->orderBy('name');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('per_page')) {
            return ApiResponse::paginated(
                $query->paginate((int) $request->input('per_page')),
                ApiMessages::FACILITY_LIST_SUCCESS
            );
        }

        return ApiResponse::success($query->get(), ApiMessages::FACILITY_LIST_SUCCESS);
    }

    public function show(string $id): JsonResponse
    {
        $facility = Facility::query()->where('id', $id)->first();

        if (!$facility) {
            return ApiResponse::notFound(ApiMessages::FACILITY_NOT_FOUND);
        }

        return ApiResponse::success($facility, ApiMessages::FACILITY_DETAIL_SUCCESS);
    }

    public function store(Request $request): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $name = Str::of((string) $request->input('name'))->squish()->title()->toString();
        $slug = Str::slug($name, '_');

        if ($slug === '') {
            return ApiResponse::validationError([
                'name' => ['Nama fasilitas tidak valid.'],
            ]);
        }

        $facility = Facility::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'id' => (string) Str::uuid7(),
                'name' => $name,
            ]
        );

        return ApiResponse::success($facility, ApiMessages::FACILITY_CREATED_SUCCESS, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $facility = Facility::query()->where('id', $id)->first();

        if (!$facility) {
            return ApiResponse::notFound(ApiMessages::FACILITY_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $name = Str::of((string) $request->input('name'))->squish()->title()->toString();
        $slug = Str::slug($name, '_');

        if ($slug === '') {
            return ApiResponse::validationError([
                'name' => ['Nama fasilitas tidak valid.'],
            ]);
        }

        $duplicate = Facility::query()
            ->where('slug', $slug)
            ->where('id', '!=', $facility->id)
            ->exists();

        if ($duplicate) {
            return ApiResponse::validationError([
                'name' => ['Nama fasilitas sudah digunakan.'],
            ]);
        }

        $facility->name = $name;
        $facility->slug = $slug;
        $facility->save();

        return ApiResponse::success($facility, ApiMessages::FACILITY_UPDATED_SUCCESS);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $facility = Facility::query()->where('id', $id)->first();

        if (!$facility) {
            return ApiResponse::notFound(ApiMessages::FACILITY_NOT_FOUND);
        }

        $facility->delete();

        return ApiResponse::success(null, ApiMessages::FACILITY_DELETED_SUCCESS);
    }

}
