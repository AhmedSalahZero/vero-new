<?php

namespace App\Http\Middleware;

use Closure;

class TradingMiddleware
{
   
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
