<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'super_admin' || in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to this module. Please login as Super Admin (superadmin@gstsaas.com) to access Governance.');
    }
}