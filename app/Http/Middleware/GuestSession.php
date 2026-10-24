<?php
namespace App\Http\Middleware;

use App\Services\CurrentCart;
use Closure;
use Illuminate\Http\Request;


class GuestSession
{
    /**
     * Make sure the visitor has a cart before adding to it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        CurrentCart::getOrCreate();

        return $next($request);
    }


}
