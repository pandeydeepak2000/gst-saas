<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Invoice;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function index()
    {
        // Platform wide metrics
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('is_active', true)->count();
        $totalUsers = User::count();
        $totalInvoices = Invoice::withoutGlobalScopes()->count();
        $totalGrossVolume = Invoice::withoutGlobalScopes()->where('status', '!=', 'cancelled')->sum('total_amount');

        // All onboarded tenants with owner & metrics
        $companies = Company::with(['users' => function($q) {
            $q->where('role', 'company_admin');
        }])->withCount(['invoices', 'users'])->latest('id')->paginate(15);

        return view('superadmin.dashboard', compact(
            'totalCompanies',
            'activeCompanies',
            'totalUsers',
            'totalInvoices',
            'totalGrossVolume',
            'companies'
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

    public function impersonate($id)
    {
        $company = Company::findOrFail($id);
        $companyAdmin = $company->users()->where('role', 'company_admin')->first();

        if (!$companyAdmin) {
            return back()->withErrors(['error' => 'No admin user found for this company.']);
        }

        // Store original super admin id in session
        session(['impersonator_id' => auth()->id()]);
        Auth::login($companyAdmin);

        return redirect()->route('dashboard')->with('info', "You are now impersonating {$company->name} as {$companyAdmin->name}.");
    }

    public function stopImpersonate()
    {
        $impersonatorId = session('impersonator_id');
        if (!$impersonatorId) {
            return redirect()->route('dashboard');
        }

        $superAdmin = User::findOrFail($impersonatorId);
        session()->forget('impersonator_id');
        Auth::login($superAdmin);

        return redirect()->route('superadmin.index')->with('success', 'Returned to Super Admin control panel.');
    }
}
