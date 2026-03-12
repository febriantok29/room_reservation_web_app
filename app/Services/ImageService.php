<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generic image upload/compress/delete service.
 *
 * Supports any module by passing a folder name (e.g. 'rooms', 'users').
 * Files are stored on the 'public' disk under storage/app/public/{folder}/
 * and served via the storage symlink at public/storage/{folder}/.
 *
 * Auto-compression: if the uploaded file exceeds MAX_SIZE_BYTES, it is
 * re-encoded as JPEG using PHP GD. Quality is reduced iteratively; if
 * quality reduction alone is insufficient, dimensions are scaled down.
 *
 * Requirements: PHP with GD extension (standard on most hosts).
 */
class ImageService
{
    /** Files above this size are auto-compressed. */
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2 MB

    /** Server-side hard limit accepted before compression (10 MB). */
    public const SERVER_MAX_BYTES = 10 * 1024 * 1024;

    /** Accepted MIME types. */
    public const ALLOWED_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Upload an image file to the given folder on the public disk.
     *
     * If the file exceeds MAX_SIZE_BYTES, it is auto-compressed to JPEG.
     * If $oldPath is provided, the old file is deleted after successful upload.
     *
     * @param  UploadedFile  $file
     * @param  string        $folder    Subfolder inside public disk (e.g. 'rooms')
     * @param  string|null   $oldPath   Existing storage path to replace (deleted on success)
     * @return array{
     *   path: string,
     *   image_id: string,
     *   url: string,
     *   size_bytes: int,
     *   was_compressed: bool
     * }
     */
    public function upload(UploadedFile $file, string $folder, ?string $oldPath = null): array
    {
        $uuid = (string) Str::uuid();
        $needsCompression = $file->getSize() > self::MAX_SIZE_BYTES;
        $outputExt = $needsCompression ? 'jpg' : strtolower($file->getClientOriginalExtension());
        $imageId = $uuid . '.' . $outputExt;
        $storagePath = $folder . '/' . $imageId;

        if ($needsCompression) {
            $compressedData = $this->compress($file->getRealPath(), strtolower($file->getClientOriginalExtension()));
            Storage::disk('public')->put($storagePath, $compressedData);
        } else {
            Storage::disk('public')->putFileAs($folder, $file, $imageId);
        }

        // Delete the old file only after successful write
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return [
            'path'           => $storagePath,
            'image_id'       => $imageId,
            'url'            => Storage::disk('public')->url($storagePath),
            'size_bytes'     => Storage::disk('public')->size($storagePath),
            'was_compressed' => $needsCompression,
        ];
    }

    /**
     * Delete an image by its storage path on the public disk.
     * No-op if the file does not exist.
     */
    public function delete(string $storagePath): void
    {
        if (Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
    }

    /**
     * Compress an image to stay under MAX_SIZE_BYTES.
     * Always outputs JPEG binary data.
     *
     * Strategy:
     *   1. Reduce JPEG quality from 85 → 30 (step −10).
     *   2. If still too large, scale dimensions down (−25% each step).
     *   3. Final fallback: return whatever is smallest.
     */
    private function compress(string $sourcePath, string $ext): string
    {
        $image = $this->loadImage($sourcePath, $ext);

        // GD not available or unsupported format — return raw bytes unchanged
        if (!$image) {
            return (string) file_get_contents($sourcePath);
        }

        $origW = imagesx($image);
        $origH = imagesy($image);

        // Phase 1: quality reduction
        for ($quality = 85; $quality >= 30; $quality -= 10) {
            $content = $this->encodeJpeg($image, $quality);
            if (strlen($content) <= self::MAX_SIZE_BYTES) {
                imagedestroy($image);
                return $content;
            }
        }

        // Phase 2: dimension scaling
        for ($scale = 0.75; $scale >= 0.25; $scale -= 0.25) {
            $newW = (int) round($origW * $scale);
            $newH = (int) round($origH * $scale);

            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            $content = $this->encodeJpeg($resized, 70);
            imagedestroy($resized);

            if (strlen($content) <= self::MAX_SIZE_BYTES) {
                break;
            }
        }

        imagedestroy($image);

        // $content is either within limit or the smallest we could achieve
        return $content ?? (string) file_get_contents($sourcePath);
    }

    /**
     * Load a GD image resource from file, handling PNG transparency.
     *
     * @return \GdImage|false
     */
    private function loadImage(string $path, string $ext)
    {
        return match ($ext) {
            'jpg', 'jpeg' => imagecreatefromjpeg($path),
            'png'         => $this->loadPngAsOpaque($path),
            'webp'        => imagecreatefromwebp($path),
            default       => false,
        };
    }

    /**
     * Load PNG onto a white canvas, removing transparency for JPEG output.
     *
     * @return \GdImage|false
     */
    private function loadPngAsOpaque(string $path)
    {
        $png = imagecreatefrompng($path);
        if (!$png) {
            return false;
        }

        $w = imagesx($png);
        $h = imagesy($png);
        $canvas = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $png, 0, 0, 0, 0, $w, $h);
        imagedestroy($png);

        return $canvas;
    }

    /**
     * Encode a GD image as JPEG and return binary string.
     *
     * @param \GdImage $image
     */
    private function encodeJpeg($image, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);
        return (string) ob_get_clean();
    }
}
