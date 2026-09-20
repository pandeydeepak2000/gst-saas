<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingsController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        return view('settings.index', compact('company'));
    }

    public function update(Request $request)
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'name'                        => ['required', 'string', 'max:255'],
            'email'                       => ['nullable', 'email', 'max:255'],
            'phone'                       => ['nullable', 'string', 'max:20'],
            'address'                     => ['nullable', 'string'],
            'city'                        => ['nullable', 'string', 'max:100'],
            'state'                       => ['required', 'string', 'max:100'],
            'pincode'                     => ['nullable', 'string', 'max:10'],
            'gstin'                       => ['nullable', 'string', 'max:20'],
            'pan'                         => ['nullable', 'string', 'max:15'],

            // Tax display toggle: 'simple' vs 'detailed'
            'tax_mode'                    => ['required', 'in:simple,detailed'],

            // Custom Invoice Numbering settings
            'invoice_prefix'              => ['required', 'string', 'max:20'],
            'invoice_start_number'        => ['required', 'integer', 'min:1'],
            'allow_manual_invoice_number' => ['nullable', 'boolean'],

            // Bank & Payment QR
            'bank_name'                   => ['nullable', 'string', 'max:150'],
            'bank_account_number'         => ['nullable', 'string', 'max:50'],
            'bank_ifsc'                   => ['nullable', 'string', 'max:20'],
            'bank_branch'                 => ['nullable', 'string', 'max:100'],
            'upi_id'                      => ['nullable', 'string', 'max:100'],
            'terms_and_conditions'        => ['nullable', 'string'],

            // Files
            'logo'                        => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'signature'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ]);

        $validated['allow_manual_invoice_number'] = $request->has('allow_manual_invoice_number');
        if (!empty($validated['gstin'])) {
            $validated['gstin'] = strtoupper($validated['gstin']);
        }
        if (!empty($validated['pan'])) {
            $validated['pan'] = strtoupper($validated['pan']);
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($company->signature_path) {
                Storage::disk('public')->delete($company->signature_path);
            }
            $validated['signature_path'] = $request->file('signature')->store('signatures', 'public');
        }

        $company->update($validated);

        ActivityLog::log('update', 'settings', "Updated company settings and billing preferences.");

        return back()->with('success', 'Company preferences and invoice settings updated successfully!');
    }
}