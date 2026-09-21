<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Invoice;
use App\Models\ActivityLog;
use App\Models\EmailChangeRequest;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'directory');

        // Platform-wide metrics
        $totalCompanies   = Company::count();
        $activeCompanies  = Company::where('is_active', true)->where('approval_status', 'approved')->count();
        $pendingCompaniesCount = Company::where('approval_status', 'pending')->count();
        $totalUsers       = User::count();
        $totalInvoices    = Invoice::withoutGlobalScopes()->count();
        $totalGrossVolume = Invoice::withoutGlobalScopes()->where('status', '!=', 'cancelled')->sum('total_amount');

        // All onboarded tenants
        $companiesQuery = Company::with(['users' => function($q) {
            $q->where('role', 'company_admin');
        }])->withCount(['invoices', 'users']);

        if ($request->filled('search')) {
            $s = $request->search;
            $companiesQuery->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        $companies = $companiesQuery->latest('id')->paginate(15);

        // Pending Approvals
        $pendingCompanies = Company::where('approval_status', 'pending')
            ->with(['users' => function($q) { $q->where('role', 'company_admin'); }])
            ->latest('id')
            ->get();

        $emailRequests = EmailChangeRequest::with(['company', 'user'])
            ->latest('id')
            ->get();

        $pendingApprovalsCount = $pendingCompaniesCount + $emailRequests->where('status', 'pending')->count();

        // Cross-Tenant Audit Logs
        $auditLogs = ActivityLog::latest('id')->take(50)->get();

        // Platform Policy
        $requireApproval = PlatformSetting::get('require_admin_approval_for_onboarding') === '1';

        return view('superadmin.dashboard', compact(
            'tab',
            'totalCompanies',
            'activeCompanies',
            'pendingCompaniesCount',
            'totalUsers',
            'totalInvoices',
            'totalGrossVolume',
            'companies',
            'pendingCompanies',
            'emailRequests',
            'pendingApprovalsCount',
            'auditLogs',
            'requireApproval'
        ));
    }

    public function toggleStatus($id)
    {
        $company = Company::findOrFail($id);
        $company->is_active = !$company->is_active;
        $company->save();

        $statusStr = $company->is_active ? 'Activated' : 'Suspended';
        ActivityLog::create([
            'company_id'  => $company->id,
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'role'        => 'super_admin',
            'action'      => 'tenant_status',
            'module'      => 'company',
            'module_id'   => $company->id,
            'description' => "Super Admin {$statusStr} company {$company->name}.",
        ]);

        return back()->with('success', "Company '{$company->name}' has been {$statusStr}.");
    }

    public function approveCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->update([
            'approval_status' => 'approved',
            'is_active'       => true,
        ]);

        ActivityLog::create([
            'company_id'  => $company->id,
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'role'        => 'super_admin',
            'action'      => 'onboarding_approval',
            'module'      => 'company',
            'module_id'   => $company->id,
            'description' => "Super Admin verified and approved onboarding for company '{$company->name}'.",
        ]);

        return back()->with('success', "Company '{$company->name}' has been approved and activated.");
    }

    public function rejectCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->update([
            'approval_status' => 'rejected',
            'is_active'       => false,
        ]);

        ActivityLog::create([
            'company_id'  => $company->id,
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'role'        => 'super_admin',
            'action'      => 'onboarding_rejection',
            'module'      => 'company',
            'module_id'   => $company->id,
            'description' => "Super Admin rejected onboarding for company '{$company->name}'.",
        ]);

        return back()->with('warning', "Company '{$company->name}' registration rejected.");
    }

    public function approveEmailChange($id)
    {
        $emailRequest = EmailChangeRequest::with(['company', 'user'])->findOrFail($id);

        if ($emailRequest->status !== 'pending') {
            return back()->with('info', 'This request has already been processed.');
        }

        $user = $emailRequest->user;
        $company = $emailRequest->company;
        $oldEmail = $emailRequest->current_email;
        $newEmail = $emailRequest->requested_email;

        // Update user and company
        $user->update(['email' => $newEmail]);
        if ($company->email === $oldEmail) {
            $company->update(['email' => $newEmail]);
        }

        $emailRequest->update([
            'status'       => 'approved',
            'actioned_by'  => auth()->id(),
            'actioned_at'  => now(),
        ]);

        ActivityLog::create([
            'company_id'  => $company->id,
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'role'        => 'super_admin',
            'action'      => 'email_approval',
            'module'      => 'security',
            'module_id'   => $emailRequest->id,
            'description' => "Super Admin approved registered email change for '{$company->name}' from {$oldEmail} to {$newEmail}.",
        ]);

        return back()->with('success', "Approved registered email change for '{$company->name}' to {$newEmail}.");
    }

    public function rejectEmailChange(Request $request, $id)
    {
        $emailRequest = EmailChangeRequest::with('company')->findOrFail($id);

        $emailRequest->update([
            'status'       => 'rejected',
            'admin_notes'  => $request->get('admin_notes', 'Request rejected by platform security review.'),
            'actioned_by'  => auth()->id(),
            'actioned_at'  => now(),
        ]);

        ActivityLog::create([
            'company_id'  => $emailRequest->company_id,
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'role'        => 'super_admin',
            'action'      => 'email_rejection',
            'module'      => 'security',
            'module_id'   => $emailRequest->id,
            'description' => "Super Admin rejected email change request for '{$emailRequest->company->name}'.",
        ]);

        return back()->with('warning', "Email change request rejected.");
    }

    public function toggleOnboardingPolicy(Request $request)
    {
        $current = PlatformSetting::get('require_admin_approval_for_onboarding') === '1';
        $new = !$current;
        PlatformSetting::set('require_admin_approval_for_onboarding', $new ? '1' : '0');

        $statusStr = $new ? 'ENABLED: New companies require Super Admin verification before activation.' : 'DISABLED: New registrations auto-activate immediately.';

        return back()->with('success', "Platform Onboarding Policy updated: {$statusStr}");
    }

    public function impersonate($id)
    {
        $company = Company::findOrFail($id);
        $companyAdmin = $company->users()->where('role', 'company_admin')->first();

        if (!$companyAdmin) {
            return back()->withErrors(['error' => 'No admin user found for this company.']);
        }

        $superAdmin = auth()->user();

        // Secure tokenized impersonation session
        $token = \Illuminate\Support\Str::random(40);
        session([
            'impersonator_id'          => $superAdmin->id,
            'impersonated_company_id'  => $company->id,
            'impersonated_user_id'     => $companyAdmin->id,
            'impersonation_token'      => $token,
            'impersonation_started_at' => now()->toIso8601String(),
            'impersonation_ip'         => request()->ip(),
            'impersonation_ua'         => request()->userAgent(),
        ]);

        ActivityLog::create([
            'company_id'  => $company->id,
            'user_id'     => $superAdmin->id,
            'user_name'   => $superAdmin->name,
            'role'        => 'super_admin',
            'action'      => 'impersonate_start',
            'module'      => 'superadmin',
            'description' => "Super Admin '{$superAdmin->name}' started impersonation of company '{$company->name}' as '{$companyAdmin->name}'. (IP: " . request()->ip() . ")",
        ]);

        Auth::login($companyAdmin);

        return redirect()->route('dashboard')->with('info', "You are now impersonating {$company->name} as {$companyAdmin->name}.");
    }

    public function stopImpersonate()
    {
        $impersonatorId = session('impersonator_id');
        $token = session('impersonation_token');

        if (!$impersonatorId || !$token) {
            return redirect()->route('dashboard');
        }

        $superAdmin = User::where('id', $impersonatorId)->where('role', 'super_admin')->first();
        if (!$superAdmin) {
            session()->flush();
            return redirect()->route('login')->withErrors(['error' => 'Invalid impersonation session.']);
        }

        $impersonatedCompanyId = session('impersonated_company_id');

        // Clear all impersonation markers
        session()->forget([
            'impersonator_id',
            'impersonated_company_id',
            'impersonated_user_id',
            'impersonation_token',
            'impersonation_started_at',
            'impersonation_ip',
            'impersonation_ua',
        ]);

        ActivityLog::create([
            'company_id'  => $impersonatedCompanyId,
            'user_id'     => $superAdmin->id,
            'user_name'   => $superAdmin->name,
            'role'        => 'super_admin',
            'action'      => 'impersonate_end',
            'module'      => 'superadmin',
            'description' => "Super Admin '{$superAdmin->name}' exited impersonation session.",
        ]);

        Auth::login($superAdmin);

        return redirect()->route('superadmin.index')->with('success', 'Returned to Super Admin control panel.');
    }
}