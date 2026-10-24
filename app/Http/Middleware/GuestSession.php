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

        // Create the session's cart or extend its expiry (2 days from now). session_id is unique,
        // so two simultaneous requests can't create duplicate carts for the same session.
        Cart::updateOrCreate(
            ['session_id' => $sessionId],
            ['expires_at' => now()->addDays(2)]
        );

        return $next($request);
    }


}
