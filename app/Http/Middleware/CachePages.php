<?php

namespace App\Http\Middleware;

use App\Services\PageCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

// Sends a stored copy of a store page when there is a current one, otherwise builds the page and stores it
// (see App\Services\PageCache). Runs inside the "web" group, after the session has started, so each visitor
// still gets their own CSRF token and cookies.
class CachePages
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $key = PageCache::key($request);
        // A signed-in admin and a page with a message for this visitor are always built fresh
        if ($key === null || $request->user() || !empty($request->session()->get('_flash.old'))) {
            return $next($request);
        }

        $token = $request->session()->token();
        $version = PageCache::version();
        $stored = Cache::get($key);

        if (is_array($stored) && ($stored['version'] ?? null) === $version && ($html = self::unpack($stored['html'] ?? null)) !== null) {
            return new Response(str_replace(PageCache::TOKEN, $token, $html), 200, [
                'Content-Type' => $stored['type'],
                'X-Page-Cache' => 'HIT',
            ]);
        }

        $response = $next($request);

        $type = (string) $response->headers->get('Content-Type');
        if ($response instanceof Response && $response->getStatusCode() === 200 && !$response->exception
            && (str_starts_with($type, 'text/html') || str_contains($type, 'xml'))
            && $token !== '' && $request->session()->token() === $token) {
            Cache::put($key, [
                'version' => $version,
                'type' => $type,
                'html' => self::pack(str_replace($token, PageCache::TOKEN, (string) $response->getContent())),
            ], now()->addMinutes(max(1, (int) config('store.page_cache_minutes'))));
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    // Compressed, then written as plain text: the live site keeps its cache in a database table whose
    // text column refuses raw binary (CACHE_STORE=database)
    private static function pack(string $html): string
    {
        return base64_encode(gzdeflate($html, 6));
    }

    private static function unpack(mixed $packed): ?string
    {
        $binary = is_string($packed) ? base64_decode($packed, true) : false;
        $html = $binary === false ? false : @gzinflate($binary);

        return $html === false ? null : $html;
    }
}
