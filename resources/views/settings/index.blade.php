@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>Company Settings & Integrations</span>
                <span class="text-xs px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 font-medium border border-brand-200">Tenant Scoped</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Configure your GST rules, payment gateways, WhatsApp business messaging, and dedicated SMTP.</p>
        </div>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. INVOICE TEMPLATE PRESET -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div>
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>Industry & Invoice Template Preset</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-brand-100 text-brand-700 font-semibold">Specialized Formats</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Choose your business vertical to automatically activate specialized fields and print layouts.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ ($company->invoice_template ?? 'standard') === 'standard' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="invoice_template" value="standard" class="sr-only" {{ ($company->invoice_template ?? 'standard') === 'standard' ? 'checked' : '' }} onchange="this.form.submit()">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                            <span>📦 Standard GST Commercial</span>
                        </span>
                        @if(($company->invoice_template ?? 'standard') === 'standard')
                        <span class="text-xs font-bold text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">Active</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">Ideal for Trading, Manufacturing, Retail, and Standard Service Providers.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ ($company->invoice_template ?? 'standard') === 'hosting_domain' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="invoice_template" value="hosting_domain" class="sr-only" {{ ($company->invoice_template ?? 'standard') === 'hosting_domain' ? 'checked' : '' }} onchange="this.form.submit()">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                            <span>🌐 Web Hosting & Domain Registrar</span>
                        </span>
                        @if(($company->invoice_template ?? 'standard') === 'hosting_domain')
                        <span class="text-xs font-bold text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">Active</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">WHMCS/Cloudflare style: Adds Domain Names, Service Subscription Periods, Billing Cycles, and SAC 998315.</p>
                </label>
            </div>
        </div>

        <!-- 2. TAX DISPLAY MODE -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div>
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>Tax Calculation & Display Preference</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-semibold">User Freedom</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Toggle between simple single tax line (e.g. 18% GST) or full split CGST/SGST/IGST breakdown.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $company->tax_mode === 'simple' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="tax_mode" value="simple" class="sr-only" {{ $company->tax_mode === 'simple' ? 'checked' : '' }}>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                            <span>Simple Unified GST (18%)</span>
                        </span>
                        @if($company->tax_mode === 'simple')
                        <span class="text-xs font-bold text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">Selected</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">Displays total GST rate as a single line item without confusing split columns.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $company->tax_mode === 'detailed' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="tax_mode" value="detailed" class="sr-only" {{ $company->tax_mode === 'detailed' ? 'checked' : '' }}>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                            <span>Detailed Split GST (CGST + SGST / IGST)</span>
                        </span>
                        @if($company->tax_mode === 'detailed')
                        <span class="text-xs font-bold text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">Selected</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">Shows legal breakdown (9% CGST + 9% SGST for intrastate, or 18% IGST for interstate).</p>
                </label>
            </div>
        </div>

        <!-- 3. CUSTOM INVOICE NUMBERING -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div>
                <h3 class="font-bold text-slate-900">Invoice Numbering & Sequence Control</h3>
                <p class="text-xs text-slate-500 mt-0.5">Customize your auto-generated invoice numbering format or allow staff to edit numbers manually.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $company->invoice_prefix) }}" 
                           placeholder="INV- or GS-2026-" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Starting Number Sequence</label>
                    <input type="number" name="invoice_start_number" value="{{ old('invoice_start_number', $company->invoice_start_number) }}" 
                           min="1" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="allow_manual_invoice_number" value="1" 
                               {{ $company->allow_manual_invoice_number ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="font-semibold text-slate-900 text-sm">Allow Manual Invoice Numbering</span>
                            <p class="text-xs text-slate-500">Staff can freely type custom invoice numbers when creating bills.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 4. ZERO-FEE DYNAMIC NPCI UPI QR CODE -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span>⚡ Zero-Fee Dynamic NPCI UPI QR Code</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">0% Gateway Cut</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Direct merchant UPI payments from PhonePe, Google Pay, Paytm, CRED, and BHIM straight into your bank account.</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="enable_upi_qr" value="1" {{ $company->enable_upi_qr ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-xs font-bold text-slate-700 uppercase">Enable UPI QR</span>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Company UPI VPA ID</label>
                    <input type="text" name="upi_id" value="{{ old('upi_id', $company->upi_id) }}" placeholder="e.g. yourbusiness@okaxis, acme@icici"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">This VPA is used to generate exact balance-due QR codes on PDF and web invoices.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Payee Name (as registered in Bank)</label>
                    <input type="text" name="upi_name" value="{{ old('upi_name', $company->upi_name ?: $company->name) }}" placeholder="e.g. Acme Infotech Private Limited"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 5. DEDICATED RAZORPAY PAYMENT GATEWAY -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4" x-data="{ showRazorpaySecret: false }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span>💳 Dedicated Razorpay Online Payment Gateway</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold border border-blue-200">Per-Tenant Gateway</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Collect Credit/Debit Cards, NetBanking, and Wallets. Money settles directly into YOUR Razorpay account.</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="enable_razorpay" value="1" {{ $company->enable_razorpay ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-xs font-bold text-slate-700 uppercase">Enable Razorpay</span>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Razorpay Key ID</label>
                    <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $company->razorpay_key_id) }}" placeholder="rzp_live_xxxxxxxxxxxxxx"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Razorpay Key Secret</label>
                    <div class="relative">
                        <input :type="showRazorpaySecret ? 'text' : 'password'" name="razorpay_key_secret" 
                               placeholder="{{ $company->razorpay_key_secret ? '••••••••••••••••' : 'Secret Key' }}"
                               class="w-full px-4 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <button type="button" @click="showRazorpaySecret = !showRazorpaySecret" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <span x-show="!showRazorpaySecret">👁️</span>
                            <span x-show="showRazorpaySecret" x-cloak>🙈</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. DEDICATED WHATSAPP BUSINESS INTEGRATION -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div>
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>💬 Dedicated WhatsApp Business Billing & Reminders</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">1-Click Share</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Customize your greeting message template. Invoice links and amounts will be dynamically inserted.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Company WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $company->whatsapp_number) }}" placeholder="+91 9876543210"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">Displayed on the invoice as your official WhatsApp contact.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Custom Message Template</label>
                    <textarea name="whatsapp_template" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none" placeholder="Dear {customer_name}, your invoice #{invoice_number} from {company_name} for ₹{total_amount} is ready. View & Pay: {public_url}">{{ old('whatsapp_template', $company->whatsapp_template) }}</textarea>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono cursor-pointer" title="Click to copy">{customer_name}</span>
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono cursor-pointer">{invoice_number}</span>
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono cursor-pointer">{total_amount}</span>
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono cursor-pointer">{balance_amount}</span>
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono cursor-pointer">{due_date}</span>
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono cursor-pointer">{public_url}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. BANK DETAILS FOR MANUAL TRANSFERS -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div>
                <h3 class="font-bold text-slate-900">Bank Account Details (NEFT / RTGS / IMPS)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Printed at the bottom of customer invoices for wire transfer settlements.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $company->bank_name) }}" placeholder="e.g. HDFC Bank, ICICI"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $company->bank_account_number) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">IFSC Code</label>
                    <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $company->bank_ifsc) }}" placeholder="HDFC0000240"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono uppercase text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Branch Name</label>
                    <input type="text" name="bank_branch" value="{{ old('bank_branch', $company->bank_branch) }}" placeholder="Connaught Place, Delhi"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 8. DEDICATED COMPANY SMTP MAIL SERVER SETTINGS -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4" x-data="{ showSmtpPass: false }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span>Dedicated Company SMTP Mail Server</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Per-Tenant Emailing</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">When configured, all client invoice notifications and staff password resets will be sent directly through YOUR mail server.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $company->mail_host) }}" placeholder="mail.mycompany.com or smtp.gmail.com"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">SMTP Port</label>
                    <input type="number" name="mail_port" value="{{ old('mail_port', $company->mail_port ?: 587) }}" placeholder="587"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Encryption</label>
                    <select name="mail_encryption" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="tls" {{ ($company->mail_encryption ?: 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587 - Recommended)</option>
                        <option value="ssl" {{ $company->mail_encryption === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                        <option value="none" {{ $company->mail_encryption === 'none' ? 'selected' : '' }}>None (Insecure)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">SMTP Username / Email</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username', $company->mail_username) }}" placeholder="billing@mycompany.com"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">SMTP Password</label>
                    <div class="relative">
                        <input :type="showSmtpPass ? 'text' : 'password'" name="mail_password" placeholder="{{ $company->mail_password ? '••••••••' : 'App Password / Secret' }}"
                               class="w-full px-3.5 py-2 pr-10 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                        <button type="button" @click="showSmtpPass = !showSmtpPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <span x-show="!showSmtpPass">👁️</span>
                            <span x-show="showSmtpPass" x-cloak>🙈</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sender Email ("From" Address)</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $company->mail_from_address ?: $company->email) }}" placeholder="billing@mycompany.com"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                </div>
            </div>
        </div>

        <!-- 9. GENERAL COMPANY PROFILE -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900">General Legal Profile</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Company Trade Name *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Company GSTIN</label>
                    <input type="text" name="gstin" value="{{ old('gstin', $company->gstin) }}" placeholder="07AAAAA0000A1Z5"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">State / UT *</label>
                    <input type="text" name="state" value="{{ old('state', $company->state) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <button type="submit" class="px-8 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all">
                Save All Company Preferences & Integrations
            </button>
        </div>
    </form>

    <!-- LIVE SMTP TEST BOX -->
    @if($company->mail_host)
    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h4 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                <span>⚡ Test Dedicated SMTP Connection</span>
            </h4>
            <p class="text-xs text-slate-500 mt-0.5">Send a real verification email through your mail server ({{ $company->mail_host }}) to confirm delivery.</p>
        </div>
        <form action="{{ route('settings.test_mail') }}" method="POST" class="flex items-center gap-2">
            @csrf
            <input type="email" name="test_email" value="{{ auth()->user()->email }}" required
                   placeholder="recipient@domain.com"
                   class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 font-mono">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold">
                Send Test Email
            </button>
        </form>
    </div>
    @endif
</div>
@endsection