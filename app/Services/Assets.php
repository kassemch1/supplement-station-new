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
        $folder = preg_replace('#^public/#', '', self::outputDir($name));

        return asset($folder . '/' . $name) . ($hash ? '?v=' . $hash : '');
    }

    // Folder of a bundle, relative to the project root: its own 'output' or the shared one
    public static function outputDir(string $name): string
    {
        return config('assets.bundles')[$name]['output'] ?? config('assets.output');
    }

    public static function manifestPath(): string
    {
        return base_path(config('assets.output') . '/manifest.json');
    }
}
