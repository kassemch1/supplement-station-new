<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

// Finds the visitor's cart. The cart used to be keyed by the login session, which expires after
// 2 hours of inactivity, so carts silently disappeared. It now has its own 30-day cookie. Visitors
// who already have a cart keep it: their session's cart key becomes their cookie on the next request.
class CurrentCart
{
    public const COOKIE = 'cart_token';
    public const DAYS = 30;

    public static function token(): string
    {
        $token = request()->cookie(self::COOKIE) ?: Session::get('session_id') ?: Str::random(40);

        // Re-issue the cookie so it stays valid for 30 days after the last visit
        if (!Cookie::hasQueued(self::COOKIE)) {
            Cookie::queue(self::COOKIE, $token, self::DAYS * 24 * 60);
        }
        Session::put('session_id', $token);

        return $token;
    }

    public static function get(): ?Cart
    {
        return Cart::where('session_id', self::token())->first();
    }

    // Creates the cart if needed and pushes its expiry 30 days out. Expired carts are cleaned up
    // here now and then, so no cron job is needed.
    public static function getOrCreate(): Cart
    {
        if (random_int(1, 50) === 1) {
            Cart::where('expires_at', '<', now())->delete();
        }

        return Cart::updateOrCreate(
            ['session_id' => self::token()],
            ['expires_at' => now()->addDays(self::DAYS)]
        );
    }
}
