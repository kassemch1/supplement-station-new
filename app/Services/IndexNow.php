<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

// IndexNow (indexnow.org): tells Bing (and Yandex, Seznam, Naver) the moment a page changes, instead of
// waiting for their next visit. A product or category the admin adds, changes or archives is sent once
// the admin's page has had its answer, so the admin never waits for it.
//
// Off until INDEXNOW_KEY is set in .env (8 to 128 letters, digits or dashes). The key is published at
// /indexnow.txt (routes/seo.php) so the search engines can check the addresses come from this site.
// php artisan seo:indexnow sends every address in the sitemap at once (after a deploy, for example).
class IndexNow
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    // One request takes at most this many addresses
    private const BATCH = 10000;

    /** @var array<string, true> addresses changed during this request */
    private static array $pending = [];

    private static bool $flushRegistered = false;

    public static function key(): ?string
    {
        $key = (string) config('store.indexnow_key');

        return preg_match('/^[A-Za-z0-9-]{8,128}$/', $key) ? $key : null;
    }

    public static function enabled(): bool
    {
        return self::key() !== null;
    }

    public static function watchModels(): void
    {
        // Products are archived (soft deleted) and can come back; categories are deleted for good
        foreach (['saved', 'deleted', 'restored'] as $event) {
            Product::{$event}(fn (Product $product) => self::queue(array_filter([$product->url(), $product->getCategory?->url()])));
        }
        foreach (['saved', 'deleted'] as $event) {
            Category::{$event}(fn (Category $category) => self::queue([$category->url()]));
        }
    }

    // Remembers the addresses and sends them all together at the end of the request
    public static function queue(array $urls): void
    {
        if (!self::enabled()) {
            return;
        }
        foreach ($urls as $url) {
            self::$pending[$url] = true;
        }
        if (!self::$flushRegistered) {
            self::$flushRegistered = true;
            app()->terminating(fn () => self::flush());
        }
    }

    public static function flush(): void
    {
        $urls = array_keys(self::$pending);
        self::$pending = [];
        self::$flushRegistered = false;
        if ($urls) {
            self::submit($urls);
        }
    }

    // Sends the addresses; returns how many were accepted. A search engine that is down never breaks the site
    public static function submit(array $urls): int
    {
        $key = self::key();
        if ($key === null || !$urls) {
            return 0;
        }

        $host = parse_url(url('/'), PHP_URL_HOST);
        $sent = 0;
        foreach (array_chunk(array_values(array_unique($urls)), self::BATCH) as $batch) {
            try {
                $response = Http::timeout(10)->acceptJson()->post(self::ENDPOINT, [
                    'host' => $host,
                    'key' => $key,
                    'keyLocation' => url('/indexnow.txt'),
                    'urlList' => $batch,
                ]);
                // 200: received, 202: received and the key is still being checked
                if (in_array($response->status(), [200, 202], true)) {
                    $sent += count($batch);
                } else {
                    Log::warning('IndexNow refused the addresses', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (Throwable $e) {
                Log::warning('IndexNow could not be reached: ' . $e->getMessage());
            }
        }

        return $sent;
    }
}
