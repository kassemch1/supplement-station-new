<?php

namespace App\Support;

// Addresses of the shop's filter buttons. Every button is a real link (it works without JavaScript and
// can be shared); the page script loads it without a reload.
class ShopUrl
{
    // Query parameters that are filters (paging and the old category parameters are not)
    private const FILTERS = ['q', 'shop-search', 'brand', 'min', 'max', 'in_stock', 'sale', 'orderby'];

    // The current filters with $changes applied (null removes one); always back to the first page
    public static function with(string $base, array $changes = []): string
    {
        $query = array_merge(self::current(), $changes);

        return self::build($base, $query);
    }

    // Adds the brand to the brand filter, or takes it out when it is already in
    public static function toggleBrand(string $base, string $slug): string
    {
        $brands = array_filter(explode(',', (string) (self::current()['brand'] ?? '')));
        $brands = in_array($slug, $brands, true) ? array_diff($brands, [$slug]) : [...$brands, $slug];

        return self::with($base, ['brand' => $brands ? implode(',', $brands) : null]);
    }

    // Only plain values: a list (?brand[]=x) would break the links and the hidden form fields
    public static function current(): array
    {
        return array_filter(array_intersect_key(request()->query(), array_flip(self::FILTERS)), 'is_scalar');
    }

    private static function build(string $base, array $query): string
    {
        $query = array_filter($query, fn ($value) => $value !== null && $value !== '' && $value !== false
            && !($value === 'default'));

        return $base . ($query ? '?' . http_build_query($query) : '');
    }
}
