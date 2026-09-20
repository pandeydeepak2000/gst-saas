<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only the Company Owner / Admin can manage team members and staff access.');
        }

        $company = $user->company;
        $staffMembers = User::where('company_id', $company->id)
            ->where('id', '!=', $user->id)
            ->latest('id')
            ->get();

        return view('staff.index', compact('company', 'staffMembers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $company = $user->company;

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:invoices,customers,products,payments,reports,settings'],
        ]);

        $staff = User::create([
            'company_id'  => $company->id,
            'name'        => $validated['name'],
            'email'       => strtolower(trim($validated['email'])),
            'password'    => Hash::make($validated['password']),
            'phone'       => $validated['phone'] ?? null,
            'role'        => 'staff',
            'permissions' => $validated['permissions'] ?? ['invoices', 'customers'],
            'is_active'   => true,
        ]);

        ActivityLog::log('create', 'staff', "Added new staff member '{$staff->name}' ({$staff->email}) with custom module permissions.");

        return back()->with('success', "Team member '{$staff->name}' created successfully with assigned permissions!");
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $staff = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'password'    => ['nullable', 'string', 'min:8'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:invoices,customers,products,payments,reports,settings'],
        ]);

        $updateData = [
            'name'        => $validated['name'],
            'phone'       => $validated['phone'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);

        ActivityLog::log('update', 'staff', "Updated permissions and details for staff '{$staff->name}'.");

        return back()->with('success', "Permissions for '{$staff->name}' updated successfully!");
    }

    public function toggleStatus($id)
    {
        $user = auth()->user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $staff = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->findOrFail($id);

        $staff->is_active = !$staff->is_active;
        $staff->save();

        $statusStr = $staff->is_active ? 'activated' : 'deactivated';
        ActivityLog::log('status_toggle', 'staff', "Staff member '{$staff->name}' {$statusStr}.");

        return back()->with('success', "Staff member '{$staff->name}' has been {$statusStr}.");
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $staff = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->findOrFail($id);

        $name = $staff->name;
        $staff->delete();

        ActivityLog::log('delete', 'staff', "Removed staff member '{$name}'.");

        return back()->with('success', "Staff member '{$name}' removed from your company workspace.");
    }
}