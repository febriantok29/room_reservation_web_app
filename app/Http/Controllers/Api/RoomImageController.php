<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Room;
use App\Services\ImageService;
use App\Support\ApiErrorCodes;
use App\Support\ApiMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class RoomImageController extends Controller
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Upload or replace the image for a room.
     *
     * POST /v1/rooms/{id}/image
     *
     * Accepts multipart/form-data with field "image".
     * Files larger than 2 MB are auto-compressed to JPEG.
     * Server accepts up to 10 MB as incoming file size.
     */
    public function store(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $room = Room::query()->where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:' . implode(',', ImageService::ALLOWED_MIMES),
                'max:' . (ImageService::SERVER_MAX_BYTES / 1024), // validate in KB
            ],
        ], [
            'image.required' => 'File gambar wajib diunggah.',
            'image.file'     => 'Upload harus berupa file.',
            'image.image'    => 'File harus berupa gambar.',
            'image.mimes'    => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'image.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        try {
            $result = $this->imageService->upload(
                $request->file('image'),
                'rooms',
                $room->image_path
            );
        } catch (Throwable) {
            return ApiResponse::error(
                ApiErrorCodes::IMAGE_UPLOAD_FAILED,
                ApiMessages::IMAGE_UPLOAD_FAILED,
                500
            );
        }

        $room->image_path = $result['path'];
        $room->updated_by = $request->user()->id;
        $room->save();

        return ApiResponse::success([
            'image_id'       => $result['image_id'],
            'image_url'      => $result['url'],
            'size_bytes'     => $result['size_bytes'],
            'was_compressed' => $result['was_compressed'],
        ], ApiMessages::ROOM_IMAGE_UPLOADED_SUCCESS, 200);
    }

    /**
     * Delete the image for a room.
     *
     * DELETE /v1/rooms/{id}/image
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($forbidden = $this->ensureAdmin($request)) {
            return $forbidden;
        }

        $room = Room::query()->where('id', $id)->whereNull('deleted_at')->first();

        if (!$room) {
            return ApiResponse::notFound(ApiMessages::ROOM_NOT_FOUND);
        }

        if (!$room->image_path) {
            return ApiResponse::error(
                ApiErrorCodes::IMAGE_NOT_FOUND,
                ApiMessages::ROOM_IMAGE_NOT_FOUND,
                404
            );
        }

        $this->imageService->delete($room->image_path);
        $room->image_path = null;
        $room->updated_by = $request->user()->id;
        $room->save();

        return ApiResponse::success(null, ApiMessages::ROOM_IMAGE_DELETED_SUCCESS, 200);
    }

    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if (!$request->user()?->canApprove()) {
            return ApiResponse::error(ApiErrorCodes::FORBIDDEN, ApiMessages::FORBIDDEN, 403);
        }

        return null;
    }
}
