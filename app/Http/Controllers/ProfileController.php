<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\EmailChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        $pendingEmailRequest = null;
        if ($company) {
            $pendingEmailRequest = EmailChangeRequest::where('company_id', $company->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();
        }

        return view('profile.index', compact('user', 'company', 'pendingEmailRequest'));
    }

    public function updateInfo(Request $request)
    {
        $user = auth()->user();

        // If user is part of a company, email is LOCKED for security against account takeover
        if ($user->company_id) {
            $validated = $request->validate([
                'name'  => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
            ]);

            $user->update([
                'name'  => $validated['name'],
                'phone' => $validated['phone'] ?? null,
            ]);

            ActivityLog::log('update', 'profile', "User '{$user->name}' updated profile details.");
            return back()->with('success', 'Profile information updated successfully.');
        }

        // Super Admin can update their own email
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);
        ActivityLog::log('update', 'profile', "Super Admin updated details.");

        return back()->with('success', 'Profile details updated.');
    }

    /**
     * Submit an official request to change the company's registered billing email
     */
    public function requestEmailChange(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return back()->with('warning', 'Company not found.');
        }

        $validated = $request->validate([
            'requested_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'reason'          => ['required', 'string', 'min:5', 'max:500'],
        ]);

        // Check for existing pending request
        $existing = EmailChangeRequest::where('company_id', $company->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('warning', 'You already have an email change request under review by Super Admin.');
        }

        EmailChangeRequest::create([
            'company_id'      => $company->id,
            'user_id'         => $user->id,
            'current_email'   => $user->email,
            'requested_email' => strtolower(trim($validated['requested_email'])),
            'reason'          => $validated['reason'],
            'status'          => 'pending',
        ]);

        ActivityLog::log('security', 'auth', "Submitted request to change registered email from {$user->email} to {$validated['requested_email']}.");

        return back()->with('success', 'Email change request submitted! Super Admin will review and verify your request.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Invalidate any active password reset tokens for this user
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Regenerate session ID to prevent fixation
        $request->session()->regenerate();

        ActivityLog::log('security', 'auth', "User '{$user->name}' changed their password.");

        return back()->with('success', 'Your password has been changed successfully!');
    }

    /**
     * Toggle Two-Factor Authentication (2FA) for current authenticated user
     * Works for Super Admin, Company Admin, and Staff members
     */
    public function toggle2fa(Request $request)
    {
        $user = auth()->user();
        $newState = !$user->is_2fa_enabled;

        $user->update([
            'is_2fa_enabled'        => $newState,
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);

        $statusStr = $newState ? 'ENABLED' : 'DISABLED';

        ActivityLog::create([
            'company_id'  => $user->company_id,
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'role'        => $user->role,
            'action'      => '2fa_toggle',
            'module'      => 'security',
            'description' => "User '{$user->name}' {$statusStr} Two-Factor Authentication (2FA).",
        ]);

        $message = $newState 
            ? 'Two-Factor Authentication (2FA) is now ENABLED! You will receive a verification code on your email during every login.'
            : 'Two-Factor Authentication (2FA) has been DISABLED. You can now login directly with your password.';

        return back()->with('success', $message);
    }

    public function updateSignature(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return back()->with('warning', 'Company profile not found.');
        }

        // Restrict to safe raster images: no raw SVG XSS vectors
        $validated = $request->validate([
            'signature_file'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'digital_signature_text' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->boolean('remove_signature')) {
            if ($company->signature_path) {
                Storage::disk('public')->delete($company->signature_path);
            }
            $company->update([
                'signature_path'         => null,
                'digital_signature_text' => null,
            ]);
            ActivityLog::log('update', 'signature', "Cleared company invoice signature.");
            return back()->with('success', 'Signature removed. Invoices will not display any signature placeholder.');
        }

        if ($request->hasFile('signature_file')) {
            if ($company->signature_path) {
                Storage::disk('public')->delete($company->signature_path);
            }
            $path = $request->file('signature_file')->store('signatures', 'public');
            $company->signature_path = $path;
        }

        $company->digital_signature_text = $validated['digital_signature_text'] ?? null;
        $company->save();

        ActivityLog::log('update', 'signature', "Updated company invoice signature details.");
        return back()->with('success', 'Invoice signature and digital sign-off updated successfully!');
    }
}
