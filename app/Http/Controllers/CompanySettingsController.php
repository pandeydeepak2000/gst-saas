<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\TenantMailService;
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

            // Invoice Template Preset: 'standard' or 'hosting_domain'
            'invoice_template'            => ['nullable', 'in:standard,hosting_domain'],

            // Custom Invoice Numbering settings
            'invoice_prefix'              => ['required', 'string', 'max:20'],
            'invoice_start_number'        => ['required', 'integer', 'min:1'],
            'allow_manual_invoice_number' => ['nullable', 'boolean'],

            // Dedicated Company SMTP Mail Settings
            'mail_host'                   => ['nullable', 'string', 'max:255'],
            'mail_port'                   => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username'               => ['nullable', 'string', 'max:255'],
            'mail_password'               => ['nullable', 'string', 'max:255'],
            'mail_encryption'             => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_from_address'           => ['nullable', 'email', 'max:255'],
            'mail_from_name'              => ['nullable', 'string', 'max:255'],

            // Bank Details
            'bank_name'                   => ['nullable', 'string', 'max:150'],
            'bank_account_number'         => ['nullable', 'string', 'max:50'],
            'bank_ifsc'                   => ['nullable', 'string', 'max:20'],
            'bank_branch'                 => ['nullable', 'string', 'max:100'],

            // Dynamic Zero-Fee UPI Payment QR Settings
            'upi_id'                      => ['nullable', 'string', 'max:100'],
            'upi_name'                    => ['nullable', 'string', 'max:150'],
            'enable_upi_qr'               => ['nullable', 'boolean'],

            // Dedicated Razorpay Online Payment Gateway
            'razorpay_key_id'             => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret'         => ['nullable', 'string', 'max:255'],
            'enable_razorpay'             => ['nullable', 'boolean'],

            // Dedicated WhatsApp Business Integration
            'whatsapp_number'             => ['nullable', 'string', 'max:25'],
            'whatsapp_template'           => ['nullable', 'string'],

            'terms_and_conditions'        => ['nullable', 'string'],

            // Files
            'logo'                        => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'signature'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ]);

        $validated['allow_manual_invoice_number'] = $request->has('allow_manual_invoice_number');
        $validated['enable_upi_qr'] = $request->has('enable_upi_qr');
        $validated['enable_razorpay'] = $request->has('enable_razorpay');

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

        // Keep existing password if not provided
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }
        if (empty($validated['razorpay_key_secret'])) {
            unset($validated['razorpay_key_secret']);
        }

        $company->update($validated);

        ActivityLog::log('update', 'settings', "Updated company settings, payment gateways, UPI QR, WhatsApp, and SMTP credentials.");

        return back()->with('success', 'Company preferences, payment integrations, and billing settings updated successfully!');
    }

    public function sendTestMail(Request $request)
    {
        $company = auth()->user()->company;
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $result = TenantMailService::sendTestEmail($company, $request->test_email);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('warning', $result['message']);
    }
}