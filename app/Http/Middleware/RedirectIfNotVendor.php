<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotVendor
{
    public function handle($request, Closure $next, $guard = 'vendor')
    {
        if (!Auth::guard($guard)->check()) {
            return redirect()->route('Auth.loginvendor'); 
        }

        return $next($request);
    }
}

