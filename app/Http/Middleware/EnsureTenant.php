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

        // Super Admin handling
        if ($user->role === 'super_admin') {
            // If Super Admin is NOT currently impersonating a company, prevent accessing tenant-specific routes
            if (!session('impersonator_id')) {
                if ($request->routeIs('invoices.*', 'customers.*', 'products.*', 'settings.*', 'reports.*', 'dashboard', 'home')) {
                    return redirect()->route('superadmin.index')->with('info', 'You are in Super Admin mode. Please select a company from the directory below to access its billing portal.');
                }
            }
            return $next($request);
        }

        // Verify company exists
        if (!$user->company_id || !$user->company) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your company account is not configured. Please contact platform support.'
            ]);
        }

        // Handle Pending Approval (When platform onboarding verification is active)
        if ($user->company->approval_status === 'pending') {
            if ($request->routeIs('company.pending', 'logout')) {
                return $next($request);
            }
            return redirect()->route('company.pending');
        }

        // Verify company is active & approved
        if (!$user->company->is_active || $user->company->approval_status === 'rejected') {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your company account has been suspended or rejected. Please contact platform administration.'
            ]);
        }

        return $next($request);
    }
}