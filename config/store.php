<?php

// The shop's contact number, shown on every page, in the order emails and to search engines.
// Change it here only.

return [

    // As customers read it
    'phone' => '+961 81 088 266',

    // For tel: links
    'phone_link' => '+96181088266',

    // For wa.me links (country code, no + or spaces)
    'whatsapp' => '96181088266',

    // Store pages (home, shop, categories, products) are kept ready-made for this many minutes, or until
    // something they show changes (App\Services\PageCache). On by default on the live site only;
    // PAGE_CACHE=false in .env turns it off at once (then run php artisan config:cache)
    'page_cache' => (bool) env('PAGE_CACHE', env('APP_ENV') === 'production'),
    'page_cache_minutes' => (int) env('PAGE_CACHE_MINUTES', 10),

];
