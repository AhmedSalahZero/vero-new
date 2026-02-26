<?php

namespace App\Http\Middleware;

use Closure;

class PropertyManagementServiceMiddleware
{
   
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
