<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        // Invoices Query (automatically scoped to tenant via TenantScope)
        $invoicesQuery = Invoice::query();

        $totalRevenue = (clone $invoicesQuery)->where('status', '!=', 'cancelled')->sum('total_amount');
        $paidAmount   = (clone $invoicesQuery)->where('status', 'paid')->sum('total_amount');
        $unpaidAmount = (clone $invoicesQuery)->where('status', 'unpaid')->sum('total_amount');
        $totalTaxes   = (clone $invoicesQuery)->where('status', '!=', 'cancelled')
            ->selectRaw('SUM(cgst_amount + sgst_amount + igst_amount) as total_tax')
            ->value('total_tax') ?? 0;

        $invoicesCount  = (clone $invoicesQuery)->count();
        $customersCount = Customer::count();
        $trashCount     = Invoice::onlyTrashed()->count();

        // Recent Invoices
        $recentInvoices = Invoice::with('customer')
            ->latest('id')
            ->take(6)
            ->get();

        // Recent Activity
        $recentLogs = ActivityLog::latest('id')->take(6)->get();

        return view('dashboard', compact(
            'company',
            'user',
            'totalRevenue',
            'paidAmount',
            'unpaidAmount',
            'totalTaxes',
            'invoicesCount',
            'customersCount',
            'trashCount',
            'recentInvoices',
            'recentLogs'
        ));
    }
}