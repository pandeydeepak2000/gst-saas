<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin does not strictly require a company_id
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Verify user has an active company
        if (!$user->company_id || !$user->company || !$user->company->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your company account is inactive or not configured. Please contact platform support.'
            ]);
        }

        return $next($request);
    }
}