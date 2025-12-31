<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;

class SetCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $CompanyMiddle= Company::find($request->segment(2));
        return $next($request);
    }
}
