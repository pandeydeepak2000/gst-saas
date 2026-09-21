<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\PlatformSetting;
use App\Models\EmailOtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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

        $user = User::where('email', strtolower(trim($credentials['email'])))->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Your account is deactivated. Contact administrator.']);
        }

        if ($user->company_id && (!$user->company || !$user->company->is_active)) {
            return back()->withErrors(['email' => 'Your company subscription or access is currently inactive.']);
        }

        // Check if 2FA is enabled for this user (Super Admin, Company Admin, or Staff)
        if ($user->is_2fa_enabled) {
            // Generate 6-digit OTP
            $otp = (string) random_int(100000, 999999);
            $user->update([
                'two_factor_code'       => Hash::make($otp),
                'two_factor_expires_at' => now()->addMinutes(10),
            ]);

            session([
                '2fa:user:id'  => $user->id,
                '2fa:remember' => $request->boolean('remember'),
                '2fa:preview'  => (app()->isLocal() || app()->environment('testing')) ? $otp : null,
            ]);

            // Dispatch Email with OTP
            try {
                Mail::raw("Your GST-SaaS 2FA login verification code is: {$otp}\n\nThis 6-digit code expires in 10 minutes.\n\nDo not share this code with anyone.", function ($message) use ($user) {
                    $message->to($user->email)->subject("Your 6-Digit 2FA Login Code - GST-SaaS");
                });
            } catch (\Throwable $e) {
                Log::warning("Could not send 2FA OTP to {$user->email}: " . $e->getMessage());
            }

            Log::info("2FA OTP generated for {$user->email}: {$otp}");

            ActivityLog::create([
                'company_id'  => $user->company_id,
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'role'        => $user->role,
                'action'      => '2fa_challenge',
                'module'      => 'auth',
                'description' => "Two-Factor authentication code dispatched to {$user->email}.",
            ]);

            return redirect()->route('login.2fa')->with('info', "A 6-digit 2FA code has been sent to your email ({$user->email}).");
        }

        // Direct Login when 2FA is OFF
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        ActivityLog::log('login', 'auth', 'User logged in successfully');
        return redirect()->intended(route('dashboard'));
    }

    public function show2fa(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $userId = session('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget(['2fa:user:id', '2fa:remember', '2fa:preview']);
            return redirect()->route('login');
        }

        $otpPreview = session('2fa:preview');

        return view('auth.two-factor', compact('user', 'otpPreview'));
    }

    public function verify2fa(Request $request)
    {
        $userId = session('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please sign in again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget(['2fa:user:id', '2fa:remember', '2fa:preview']);
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!$user->two_factor_code || !$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            return back()->withErrors(['code' => 'The verification code has expired. Please click resend to get a new code.']);
        }

        if (!Hash::check($validated['code'], $user->two_factor_code)) {
            return back()->withErrors(['code' => 'Invalid 6-digit verification code. Please check and try again.']);
        }

        // Clear used 2FA code
        $user->update([
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);

        $remember = session('2fa:remember', false);
        session()->forget(['2fa:user:id', '2fa:remember', '2fa:preview']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        ActivityLog::log('login', 'auth', 'User authenticated via 2FA successfully');

        return redirect()->intended(route('dashboard'))->with('success', 'Logged in successfully with 2FA verification.');
    }

    public function resend2fa(Request $request)
    {
        $userId = session('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $otp = (string) random_int(100000, 999999);
        $user->update([
            'two_factor_code'       => Hash::make($otp),
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        session(['2fa:preview' => (app()->isLocal() || app()->environment('testing')) ? $otp : null]);

        try {
            Mail::raw("Your new GST-SaaS 2FA login verification code is: {$otp}\n\nThis 6-digit code expires in 10 minutes.\n\nDo not share this code with anyone.", function ($message) use ($user) {
                $message->to($user->email)->subject("Your Resent 6-Digit 2FA Login Code - GST-SaaS");
            });
        } catch (\Throwable $e) {
            Log::warning("Could not resend 2FA OTP to {$user->email}: " . $e->getMessage());
        }

        Log::info("Resent 2FA OTP for {$user->email}: {$otp}");

        return back()->with('success', "A new 6-digit verification code has been dispatched to {$user->email}.");
    }

    public function cancel2fa(Request $request)
    {
        session()->forget(['2fa:user:id', '2fa:remember', '2fa:preview']);
        return redirect()->route('login')->with('info', '2FA authentication cancelled. Please sign in again.');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function sendRegistrationOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ], [
            'email.unique' => 'This business email is already registered. Please sign in instead.',
        ]);

        $email = strtolower(trim($validated['email']));

        try {
            // Generates 4-digit code, enforces 60s cooldown, 10m expiry
            $otp = EmailOtp::generateFor($email);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);
        }

        try {
            Mail::raw("Your GST-SaaS business onboarding verification code is: {$otp}\n\nThis 4-digit code expires in 10 minutes. Do not share this OTP with anyone.", function ($message) use ($email) {
                $message->to($email)->subject("Your 4-Digit Onboarding Code - GST-SaaS");
            });
        } catch (\Throwable $e) {
            Log::warning("Could not send onboarding OTP to {$email}: " . $e->getMessage());
        }

        return response()->json([
            'success'     => true,
            'message'     => "Verification code sent to {$email}.",
            // NEVER reveal OTP in production! Only in local development environment
            'otp_preview' => app()->isLocal() ? $otp : null,
        ]);
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
            'otp'          => ['required', 'string', 'size:4'],
        ]);

        if (!EmailOtp::verify($validated['email'], $validated['otp'])) {
            return back()->withInput()->withErrors([
                'otp' => 'Invalid or expired 4-digit verification code. Please request a new OTP.',
            ]);
        }

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
            'phone'                       => $validated['phone'] ?? null,
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
            'name'              => $validated['admin_name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'company_id'        => $company->id,
            'role'              => 'company_admin',
            'phone'             => $validated['phone'] ?? null,
            'email_verified_at' => now(),
            'is_active'         => true,
            'is_2fa_enabled'    => false,
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
