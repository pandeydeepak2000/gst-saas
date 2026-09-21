<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin has master bypass
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Company Admin has master permission for all company modules
        if ($user->isCompanyAdmin()) {
            return $next($request);
        }

        // Evaluate staff against granted permissions array
        if (!$user->hasPermission($module)) {
            abort(403, "Access denied: You do not have permission to access the {$module} module.");
        }

        return $next($request);
    }
}