<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $query = Customer::where('company_id', $companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        $customers = $query->withCount('invoices')->latest('id')->paginate(15);
        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:255'],
            'gstin'            => ['nullable', 'string', 'max:20'],
            'state'            => ['required', 'string', 'max:100'],
            'billing_address'  => ['nullable', 'string'],
            'city'             => ['nullable', 'string', 'max:100'],
            'pincode'          => ['nullable', 'string', 'max:10'],
        ]);

        // Server-side assignment only
        $validated['company_id'] = auth()->user()->company_id;
        if (!empty($validated['gstin'])) {
            $validated['gstin'] = strtoupper($validated['gstin']);
        }

        $customer = Customer::create($validated);
        ActivityLog::log('create', 'customer', "Added customer: {$customer->name}", $customer->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return back()->with('success', "Customer '{$customer->name}' created successfully!");
    }

    public function update(Request $request, Customer $customer)
    {
        $user = auth()->user();
        if ($customer->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized customer access: This client does not belong to your company.');
        }

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:255'],
            'gstin'            => ['nullable', 'string', 'max:20'],
            'state'            => ['required', 'string', 'max:100'],
            'billing_address'  => ['nullable', 'string'],
            'city'             => ['nullable', 'string', 'max:100'],
            'pincode'          => ['nullable', 'string', 'max:10'],
        ]);

        // Ensure company_id cannot be overwritten
        unset($validated['company_id']);

        if (!empty($validated['gstin'])) {
            $validated['gstin'] = strtoupper($validated['gstin']);
        }

        $customer->update($validated);
        ActivityLog::log('update', 'customer', "Updated customer details: {$customer->name}", $customer->id);

        return back()->with('success', "Customer '{$customer->name}' updated successfully!");
    }

    public function destroy(Customer $customer)
    {
        $user = auth()->user();
        if ($customer->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized customer access: This client does not belong to your company.');
        }

        $name = $customer->name;
        $customer->delete();
        ActivityLog::log('delete', 'customer', "Soft deleted customer: {$name}");

        return back()->with('success', "Customer '{$name}' removed.");
    }
}