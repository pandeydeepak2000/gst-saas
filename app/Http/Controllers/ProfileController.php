<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;
        return view('profile.index', compact('user', 'company'));
    }

    public function updateInfo(Request $request)
    {
        $user = auth()->user();

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

        $oldEmail = $user->email;
        $user->update($validated);

        $msg = "Profile updated.";
        if ($oldEmail !== $user->email) {
            $msg = "Profile and login email updated to {$user->email}.";
        }

        ActivityLog::log('update', 'profile', "User '{$user->name}' updated profile details (Email: {$user->email}).");

        return back()->with('success', $msg);
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

        ActivityLog::log('security', 'auth', "User '{$user->name}' successfully changed their account password.");

        return back()->with('success', 'Your password has been changed successfully!');
    }
}