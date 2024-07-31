<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;

class GuestSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
{
    // Start the session if it doesn't exist
    if (!Session::has('session_id')) {
        Session::put('session_id', Session::getId());
    }

    // Get the session ID
    $sessionId = Session::get('session_id');

    // Check if a cart exists for this session, and create one if it doesn't
    $cart = Cart::firstOrCreate(['session_id' => $sessionId]);

    $this->cleanupExpiredCarts();

    return $next($request);
}

protected function cleanupExpiredCarts()
{
    $currentSessionId = Session::get('session_id');
    Log::info('Current Session ID: ' . $currentSessionId);
    Log::info('Current Time: ' . now());

    // Specify the lifetime you want to use for checking expiration (in minutes)
    $expirationTime = 1; // 1 minute

    // Fetch expired carts based on the specified expiration time
    $expiredCarts = Cart::where('session_id', '!=', $currentSessionId)
        ->where('updated_at', '<', now()->subMinutes($expirationTime)) // Check if updated_at is less than current time - expiration time
        ->get();

    Log::info('Found expired carts count: ' . $expiredCarts->count());

    // Delete the expired carts
    foreach ($expiredCarts as $cart) {
        Log::info('Deleting cart ID: ' . $cart->id . ' with updated_at: ' . $cart->updated_at);
        $cart->delete();
    }

    Log::info('Expired carts deleted.');
}


}
