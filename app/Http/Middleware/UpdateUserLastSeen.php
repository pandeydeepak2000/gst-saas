<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Throttle database update to once every 2 minutes per user
            if (!$user->last_seen_at || $user->last_seen_at->lessThan(now()->subMinutes(2))) {
                $user->timestamps = false;
                $user->last_seen_at = now();
                $user->last_ip = $request->ip();
                $user->save();
            }
        }

        return $next($request);
    }
}
