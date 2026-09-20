<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is deactivated. Contact administrator.']);
            }

            if ($user->company_id && (!$user->company || !$user->company->is_active)) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your company subscription or access is currently inactive.']);
            }

            ActivityLog::log('login', 'auth', 'User logged in successfully');
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function registerCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'admin_name'   => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'state'        => ['required', 'string', 'max:100'],
            'gstin'        => ['nullable', 'string', 'max:20'],
            'tax_mode'     => ['required', 'in:simple,detailed'],
        ]);

        // Generate clean company slug
        $baseSlug = Str::slug($validated['company_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Generate prefix from initials
        $words = explode(' ', trim($validated['company_name']));
        $prefix = '';
        foreach ($words as $w) {
            $prefix .= strtoupper(substr($w, 0, 1));
        }
        $prefix = substr($prefix, 0, 4) . '-';

        $company = Company::create([
            'name'                        => $validated['company_name'],
            'slug'                        => $slug,
            'email'                       => $validated['email'],
            'phone'                       => $validated['phone'],
            'state'                       => $validated['state'],
            'gstin'                       => strtoupper($validated['gstin'] ?? ''),
            'tax_mode'                    => $validated['tax_mode'],
            'invoice_prefix'              => $prefix,
            'invoice_start_number'        => 1,
            'allow_manual_invoice_number' => true,
            'is_active'                   => true,
        ]);

        $user = User::create([
            'name'       => $validated['admin_name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'company_id' => $company->id,
            'role'       => 'company_admin',
            'phone'      => $validated['phone'],
            'is_active'  => true,
        ]);

        Auth::login($user);
        ActivityLog::log('register', 'auth', "New company '{$company->name}' onboarded.");

        return redirect()->route('dashboard')->with('success', "Welcome to GST-SaaS! Your company '{$company->name}' is ready.");
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::log('logout', 'auth', 'User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }
}