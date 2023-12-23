<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class isSuperAdmin
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
        // check if its superadmin
        if (Auth::user()->id == 1) {
            return $next($request);
        } elseif (Auth::user()->id == 115 || Auth::user()->name == 'Tirani Susanti') {
            return $next($request);
        } else {
            return back()->with('error', 'Access denied');
        }
    }
}
