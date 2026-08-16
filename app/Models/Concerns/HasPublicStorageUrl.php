<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasPublicStorageUrl
{
    /**
     * Build a public storage URL using the current request's host instead of the
     * static APP_URL config, so the URL stays reachable regardless of which host
     * (LAN IP, localhost, tunnel domain) the client actually used to reach the API.
     */
    protected function publicStorageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $configuredUrl = Storage::disk('public')->url($path);

        if (app()->runningInConsole()) {
            return $configuredUrl;
        }

        $urlPath = parse_url($configuredUrl, PHP_URL_PATH);
        return $urlPath ? request()->getSchemeAndHttpHost() . $urlPath : $configuredUrl;
    }
}
