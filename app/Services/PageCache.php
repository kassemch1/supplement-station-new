<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Option;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\Review;
use App\Models\SpecialOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

// Ready-made store pages. Building a shop or product page takes the server most of its answer time; a
// stored copy is sent back in a fraction of it. The pages are the same for every visitor except the
// CSRF token, which is put back for each visitor (see App\Http\Middleware\CachePages).
//
// A copy is used only while it is current: anything a page shows that changes in the admin (the models in
// SHOWN) starts a new version, a deploy that rebuilds the CSS/JS does too, and every copy expires after
// config('store.page_cache_minutes') whatever happens.
class PageCache
{
    // The pages kept (route names)
    public const ROUTES = ['home', 'shop', 'shop.category', 'products.show', 'sitemap'];

    // What these pages show: a change to any of them makes every stored page out of date
    public const SHOWN = [
        Product::class, ProductImage::class, ProductOption::class, Option::class, Category::class,
        Brand::class, Review::class, Banner::class, Faq::class, SpecialOffer::class,
    ];

    // Stands in for the visitor's CSRF token in a stored page
    public const TOKEN = '__ss_csrf_token__';

    private const VERSION_KEY = 'store.page_version';

    public static function enabled(): bool
    {
        return (bool) config('store.page_cache');
    }

    // Changing a model in SHOWN (through Eloquent) starts a new version
    public static function watchModels(): void
    {
        foreach (self::SHOWN as $model) {
            foreach (['saved', 'deleted', 'restored', 'forceDeleted'] as $event) {
                Event::listen("eloquent.{$event}: {$model}", fn () => self::flush());
            }
        }
    }

    // Every stored page is out of date (the copies are replaced as they are next asked for)
    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, Str::random(12));
    }

    // Where this request's page is stored, or null when it is not one to keep
    public static function key(Request $request): ?string
    {
        if (!self::enabled() || !$request->isMethodCacheable() || $request->ajax() || $request->wantsJson()) {
            return null;
        }
        if (!in_array($request->route()?->getName(), self::ROUTES, true)) {
            return null;
        }
        if (!self::plainQuery($request->query())) {
            return null;
        }

        $query = $request->query();
        ksort($query);

        // One entry per address, overwritten in place, so old versions never pile up in the cache
        return 'store.page.' . md5($request->getSchemeAndHttpHost() . '/' . trim($request->path(), '/') . '?' . http_build_query($query));
    }

    // The version a stored page must carry to be used: the admin's changes and the built CSS/JS
    public static function version(): string
    {
        $version = Cache::rememberForever(self::VERSION_KEY, fn () => Str::random(12));

        return $version . '|' . @filemtime(Assets::manifestPath());
    }

    // Only plain lists are kept: page numbers, a sort and one brand. Search results and other filters are
    // built each time (the shop changes them without reloading the page anyway), so made-up addresses can't
    // fill the cache
    private static function plainQuery(array $query): bool
    {
        foreach ($query as $name => $value) {
            $ok = is_string($value) && match ($name) {
                'page' => preg_match('/^[1-9]\d{0,2}$/', $value) === 1,
                'sort' => array_key_exists($value, Catalog::SORTS),
                'brand' => Brand::cachedAll()->contains('slug', $value),
                default => false,
            };
            if (!$ok) {
                return false;
            }
        }

        return true;
    }
}
