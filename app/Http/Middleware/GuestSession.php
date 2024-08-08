<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;


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
        if (!Session::has('session_id')) {
            Session::put('session_id', Session::getId());
        }

        $sessionId = Session::get('session_id');

        // Calculate expiry time (2 days from now)
        $expiryTime = now()->addDays(2);
//        dd($expiryTime);
        // First, attempt to find an existing cart
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            // If cart doesn't exist, create it with expiry time
            Cart::create([
                'session_id' => $sessionId,
                'expires_at' => $expiryTime,
            ]);
        } else {
            // Optionally, update the expiry time if you want to extend it
            $cart->update(['expires_at' => $expiryTime]);
        }

        return $next($request);
    }


}
