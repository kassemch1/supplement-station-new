<?php

namespace App\Console\Commands;

use App\Services\IndexNow;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

// Sends every address in the sitemap to IndexNow (Bing and the others), e.g. right after a deploy
class SubmitToIndexNow extends Command
{
    protected $signature = 'seo:indexnow';

    protected $description = 'Tell Bing and the other IndexNow search engines about every page in the sitemap';

    public function handle(): int
    {
        if (!IndexNow::enabled()) {
            $this->error('Set INDEXNOW_KEY in .env first (8 to 128 letters, digits or dashes), then run php artisan config:cache.');

            return self::FAILURE;
        }

        // The same list the sitemap gives search engines
        $xml = (string) app()->handle(Request::create(url('/sitemap.xml')))->getContent();
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $matches);
        $urls = array_map(fn ($url) => html_entity_decode($url, ENT_QUOTES | ENT_XML1), $matches[1]);

        if (!$urls) {
            $this->error('The sitemap has no addresses.');

            return self::FAILURE;
        }

        $sent = IndexNow::submit($urls);
        $this->info("Sent {$sent} of " . count($urls) . ' addresses to IndexNow.');

        return $sent === count($urls) ? self::SUCCESS : self::FAILURE;
    }
}
