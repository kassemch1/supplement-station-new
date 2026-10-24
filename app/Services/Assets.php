<?php

namespace App\Services;

// URLs of the built CSS/JS bundles (see config/assets.php). Each URL carries a hash of the file's
// content, so browsers and the CDN can keep the file for a year and still get a new one after a deploy.
class Assets
{
    private static ?array $manifest = null;

    public static function url(string $name): string
    {
        self::$manifest ??= json_decode((string) @file_get_contents(self::manifestPath()), true) ?: [];

        $hash = self::$manifest[$name] ?? null;

        return asset('assets/build/' . $name) . ($hash ? '?v=' . $hash : '');
    }

    public static function manifestPath(): string
    {
        return base_path(config('assets.output') . '/manifest.json');
    }
}
