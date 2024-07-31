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

    Cart::firstOrCreate(['session_id' => $sessionId]);

    return $next($request);
}


}
