<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// Knows which uploaded image files the database still uses, so a file is only removed when nothing
// points at it anymore. Order lines (order_items.product_image) keep pointing at the picture the
// customer saw, even after the product photo was replaced, so those files must stay.
class ImageFiles
{
    // Folders on the public disk that hold uploaded pictures
    public const DIRECTORIES = ['products_images', 'banner_picture'];

    // All image paths in use, as keys: ['storage/products_images/abc.png' => true, ...]
    public static function referencedPaths(): array
    {
        $paths = DB::table('product_images')->pluck('url')
            ->merge(DB::table('banners')->whereNotNull('image')->pluck('image'))
            ->merge(DB::table('order_items')->whereNotNull('product_image')->distinct()->pluck('product_image'))
            ->merge(DB::table('image_conversions')->pluck('old_url'))
            ->merge(DB::table('image_conversions')->pluck('new_url'));

        return $paths->filter()->mapWithKeys(fn ($path) => [$path => true])->all();
    }

    // Product descriptions are free text and could contain an <img> pointing at an upload
    public static function mentionedInDescriptions(string $fileName): bool
    {
        return DB::table('products')->where('description', 'like', '%'.self::escapeLike($fileName).'%')->exists();
    }

    public static function isReferenced(string $url): bool
    {
        return DB::table('product_images')->where('url', $url)->exists()
            || DB::table('banners')->where('image', $url)->exists()
            || DB::table('order_items')->where('product_image', $url)->exists()
            || DB::table('image_conversions')->where('old_url', $url)->orWhere('new_url', $url)->exists()
            || self::mentionedInDescriptions(basename($url));
    }

    // 'storage/products_images/abc.png' -> 'products_images/abc.png' (path on the public disk), or null
    // for anything outside the upload folders
    public static function diskPath(?string $url): ?string
    {
        if (!$url || !str_starts_with($url, 'storage/') || str_contains($url, '..')) {
            return null;
        }
        $path = substr($url, strlen('storage/'));
        $directory = strstr($path, '/', true);

        return in_array($directory, self::DIRECTORIES, true) ? $path : null;
    }

    // Call after a product_images row was deleted or a banner picture was replaced: removes the file
    // (and the original it was converted from) unless something else still uses it.
    public static function forget(string $table, int $rowId, ?string $url): void
    {
        $conversions = DB::table('image_conversions')->where('table_name', $table)->where('row_id', $rowId);
        $originals = (clone $conversions)->pluck('old_url');
        $conversions->delete();

        foreach ($originals->push($url)->filter()->unique() as $path) {
            self::deleteIfUnused($path);
        }
    }

    public static function deleteIfUnused(string $url): bool
    {
        $path = self::diskPath($url);
        if ($path === null || self::isReferenced($url)) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
