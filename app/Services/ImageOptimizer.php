<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Turns uploaded product/banner pictures into smaller WebP files with GD. When the server can't convert
// a file (no GD/WebP, animated GIF, huge or broken image) the original is stored as before, so an upload
// never fails because of this.
class ImageOptimizer
{
    public const PRODUCT_MAX_SIZE = 1200;
    public const BANNER_MAX_SIZE = 1920;
    public const OPTION_MAX_SIZE = 600;
    public const LOGO_MAX_SIZE = 400;
    public const CATEGORY_MAX_SIZE = 256;

    private const QUALITY = 82;
    // A 25 megapixel image needs ~100 MB of memory in GD, more than shared hosting usually allows
    private const MAX_PIXELS = 25_000_000;

    public static function supported(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring')
            && (imagetypes() & IMG_WEBP);
    }

    // Saves the upload on the public disk and returns the path stored in the database,
    // e.g. storage/products_images/abc.webp (same style as the old $file->store() paths).
    // With $removeBackground a plain white background around the product is made transparent.
    public static function storeUpload(UploadedFile $file, string $directory, int $maxSize, bool $removeBackground = false): string
    {
        $webp = self::toWebp($file->getRealPath(), $maxSize, $removeBackground);

        if ($webp !== null) {
            $path = $directory.'/'.Str::random(40).'.webp';
            Storage::disk('public')->put($path, $webp);
        } else {
            $path = $file->store($directory, 'public');
        }

        return 'storage/'.$path;
    }

    // WebP bytes of the image scaled down to fit $maxSize x $maxSize, or null when it can't be converted.
    public static function toWebp(string $sourcePath, int $maxSize, bool $removeBackground = false): ?string
    {
        if (!self::supported()) {
            return null;
        }

        $info = @getimagesize($sourcePath);
        if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > self::MAX_PIXELS) {
            return null;
        }
        $type = $info[2];
        $needsResize = max($info[0], $info[1]) > $maxSize;
        // GIFs may be animated, and a WebP that is already small enough is left as it is (unless its
        // background has to go)
        if (!in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
            || ($type === IMAGETYPE_WEBP && !$needsResize && !$removeBackground)) {
            return null;
        }

        $image = @imagecreatefromstring(file_get_contents($sourcePath));
        if (!$image) {
            return null;
        }

        if ($type === IMAGETYPE_JPEG) {
            $image = self::applyExifOrientation($image, $sourcePath);
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = $maxSize / max($width, $height);
        if ($scale < 1) {
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $resized;
        }

        if ($removeBackground) {
            BackgroundRemover::removeLightBackground($image);
        }

        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $bytes = ob_get_clean();

        return $ok && $bytes !== '' ? $bytes : null;
    }

    // Phone photos are often stored sideways with an EXIF "rotate me" flag that GD ignores
    private static function applyExifOrientation(\GdImage $image, string $sourcePath): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($sourcePath);
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $angle ? (imagerotate($image, $angle, 0) ?: $image) : $image;
    }
}
