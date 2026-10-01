<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDemoAccount
{
    /**
     * Handle an incoming request.
     * Blocks write/mutate operations (POST, PUT, PATCH, DELETE) for demo accounts so that
     * public visitors cannot abuse shared demo accounts to run real billing, add real customers,
     * create staff logins, or change credentials.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDemo()) {
            // Allow logout and GET requests; block all data mutations
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && !$request->routeIs('logout')) {
                $message = '⚡ Demo Account Restriction: Creating or modifying invoices, customers, staff, or settings is disabled in this public demo. Please click "Register New Tenant" to create your own live business workspace!';

                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 403);
                }

                return back()->with('warning', $message);
            }
        }

        return $next($request);
    }
}
