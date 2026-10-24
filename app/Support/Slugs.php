<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Builds the name part of product and category URLs (/products/optimum-nutrition-whey-gold-standard-227kg)
class Slugs
{
    private const MAX_LENGTH = 80;

    // A slug made from $name that no other row of $table uses yet (archived products included),
    // e.g. "regular-shaker", then "regular-shaker-2", "regular-shaker-3"
    public static function unique(string $table, ?string $name, ?int $ignoreId, string $fallback): string
    {
        $base = self::fromName($name, $fallback);

        $taken = DB::table($table)
            ->where(fn ($query) => $query->where('slug', $base)->orWhere('slug', 'like', $base . '-%'))
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->pluck('slug')
            ->map(fn ($slug) => strtolower($slug))
            ->flip();

        $slug = $base;
        for ($n = 2; $taken->has($slug); $n++) {
            $slug = $base . '-' . $n;
        }

        return $slug;
    }

    public static function fromName(?string $name, string $fallback): string
    {
        // "2.27kg" becomes "2-27kg" rather than "227kg"
        $name = preg_replace('/(\d)[.,](\d)/', '$1-$2', (string) $name);
        $slug = trim(Str::limit(Str::slug($name), self::MAX_LENGTH, ''), '-');

        if ($slug === '') {
            return $fallback;
        }

        // An all-digit slug would look like an old /products/{id} link
        return ctype_digit($slug) ? $fallback . '-' . $slug : $slug;
    }
}
