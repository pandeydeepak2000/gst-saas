<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\PlatformSetting;
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

        $requiresApproval = PlatformSetting::get('require_admin_approval_for_onboarding') === '1';
        $approvalStatus = $requiresApproval ? 'pending' : 'approved';

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
            'approval_status'             => $approvalStatus,
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
        ActivityLog::log('register', 'auth', "New company '{$company->name}' registered with status '{$approvalStatus}'.");

        if ($requiresApproval) {
            return redirect()->route('company.pending')->with('info', "Your registration is submitted and pending Super Admin review.");
        }

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
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        $token = Str::random(60);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        // In demo/cloud environment, provide instant 1-click reset banner
        return back()->with('status', 'Password reset instructions generated!')
                     ->with('reset_url', $resetUrl);
    }

    public function showResetPassword(Request $request, $token)
    {
        $email = $request->get('email');
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        ActivityLog::create([
            'company_id'  => $user->company_id,
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'role'        => $user->role,
            'action'      => 'reset_password',
            'module'      => 'auth',
            'description' => "Account password was successfully reset via token link.",
        ]);

        return redirect()->route('login')->with('success', 'Your password has been reset! Please sign in with your new password.');
    }
}
