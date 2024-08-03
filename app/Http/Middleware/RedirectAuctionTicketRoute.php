<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectAuctionTicketRoute
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('auction/auctionTicket')) {
            return redirect()->route('vendor.dashboard');
        }

        return $next($request);
    }
}
