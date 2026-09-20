@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ activeTab: 'general' }">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Company Control & Integrations</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 font-bold border border-brand-200">Tenant Scoped</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage tax settings, invoice templates, UPI QR, payment gateway, and dedicated SMTP.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" form="settings-form" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition-all">
                💾 Save Changes
            </button>
        </div>
    </div>

    <!-- CLEAN NAVIGATION TABS -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 border-b border-slate-200">
        <button type="button" @click="activeTab = 'general'" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                :class="activeTab === 'general' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'">
            <span>🏢 Company & GST</span>
        </button>

        <button type="button" @click="activeTab = 'templates'" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                :class="activeTab === 'templates' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'">
            <span>📄 Invoice Design & Formats</span>
        </button>

        <button type="button" @click="activeTab = 'payments'" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                :class="activeTab === 'payments' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'">
            <span>💳 Bank, UPI QR & Razorpay</span>
        </button>

        <button type="button" @click="activeTab = 'comms'" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                :class="activeTab === 'comms' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'">
            <span>✉️ Dedicated SMTP & WhatsApp</span>
        </button>
    </div>

    <!-- MAIN FORM -->
    <form id="settings-form" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- TAB 1: GENERAL & GST PROFILE -->
        <div x-show="activeTab === 'general'" class="space-y-5">
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Business Identity & Tax Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Company Registered Name *</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">GSTIN Number</label>
                        <input type="text" name="gstin" value="{{ old('gstin', $company->gstin) }}" placeholder="29ABCDE1234F1Z5"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono text-xs sm:text-sm uppercase text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">PAN Card Number</label>
                        <input type="text" name="pan" value="{{ old('pan', $company->pan) }}" placeholder="ABCDE1234F"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono text-xs sm:text-sm uppercase text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Operating State *</label>
                        <input type="text" name="state" value="{{ old('state', $company->state) }}" required
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Postal Pincode</label>
                        <input type="text" name="pincode" value="{{ old('pincode', $company->pincode) }}" placeholder="560100"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Registered Address</label>
                        <input type="text" name="address" value="{{ old('address', $company->address) }}"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Tax Display Mode -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Tax Calculation & Display Mode</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $company->tax_mode === 'simple' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200' }}">
                        <input type="radio" name="tax_mode" value="simple" class="w-4 h-4 text-brand-600" {{ $company->tax_mode === 'simple' ? 'checked' : '' }}>
                        <div>
                            <span class="font-bold text-slate-900 text-xs sm:text-sm block">Simple Unified GST (18%)</span>
                            <span class="text-[11px] text-slate-500">Single line item GST.</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $company->tax_mode === 'detailed' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200' }}">
                        <input type="radio" name="tax_mode" value="detailed" class="w-4 h-4 text-brand-600" {{ $company->tax_mode === 'detailed' ? 'checked' : '' }}>
                        <div>
                            <span class="font-bold text-slate-900 text-xs sm:text-sm block">Detailed Split GST</span>
                            <span class="text-[11px] text-slate-500">CGST + SGST (9%+9%) or IGST (18%).</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Invoice Prefix -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Invoice Numbering & Prefix</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Prefix</label>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $company->invoice_prefix) }}" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Start Number</label>
                        <input type="number" name="invoice_start_number" value="{{ old('invoice_start_number', $company->invoice_start_number) }}" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="allow_manual_invoice_number" value="1" 
                                   {{ $company->allow_manual_invoice_number ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-semibold text-slate-800">Allow Manual Editing</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

                <!-- TAB 2: INVOICE TEMPLATES & DESIGN -->
        <div x-show="activeTab === 'templates'" class="space-y-5" x-cloak>
            
            <!-- Brand Color Theme (Zero Generic Blue) -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Workspace Brand Theme (Zero Generic Blue)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Customize your SaaS portal's primary aesthetic palette across navigation, action buttons, and badges.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ ($company->brand_theme ?? 'violet') === 'emerald' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" name="brand_theme" value="emerald" class="sr-only" {{ ($company->brand_theme ?? 'violet') === 'emerald' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-4 h-4 rounded-full bg-emerald-600"></span>
                            <strong class="text-xs text-slate-900 font-bold">Forest Emerald</strong>
                        </div>
                        <p class="text-[11px] text-slate-500">Deep emerald & titanium slate. Clean fintech aesthetic.</p>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ ($company->brand_theme ?? 'violet') === 'violet' ? 'border-purple-600 bg-purple-50/40 ring-2 ring-purple-500/20' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" name="brand_theme" value="violet" class="sr-only" {{ ($company->brand_theme ?? 'violet') === 'violet' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-4 h-4 rounded-full bg-purple-600"></span>
                            <strong class="text-xs text-slate-900 font-bold">Electric Amethyst</strong>
                        </div>
                        <p class="text-[11px] text-slate-500">Royal violet & cyber cyan. Next-Gen Cloud ERP look.</p>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ ($company->brand_theme ?? 'violet') === 'amber' ? 'border-amber-600 bg-amber-50/40 ring-2 ring-amber-500/20' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" name="brand_theme" value="amber" class="sr-only" {{ ($company->brand_theme ?? 'violet') === 'amber' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-4 h-4 rounded-full bg-amber-600"></span>
                            <strong class="text-xs text-slate-900 font-bold">Warm Amber</strong>
                        </div>
                        <p class="text-[11px] text-slate-500">Golden merchant bronze & slate. Premium trading feel.</p>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ ($company->brand_theme ?? 'violet') === 'rose' ? 'border-rose-600 bg-rose-50/40 ring-2 ring-rose-500/20' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" name="brand_theme" value="rose" class="sr-only" {{ ($company->brand_theme ?? 'violet') === 'rose' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-4 h-4 rounded-full bg-rose-600"></span>
                            <strong class="text-xs text-slate-900 font-bold">Titan Crimson</strong>
                        </div>
                        <p class="text-[11px] text-slate-500">Vibrant ruby crimson & obsidian. Bold executive style.</p>
                    </label>
                </div>
            </div>

            <!-- Invoice Templates (3 Formats) -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Invoice Visual Templates (3 Formats)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ ($company->invoice_design_template ?? 'modern') === 'modern' ? 'border-brand-600 bg-brand-50/30' : 'border-slate-200' }}">
                        <input type="radio" name="invoice_design_template" value="modern" class="sr-only" {{ ($company->invoice_design_template ?? 'modern') === 'modern' ? 'checked' : '' }}>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs sm:text-sm text-slate-900">✨ Modern Executive</span>
                            @if(($company->invoice_design_template ?? 'modern') === 'modern')
                            <span class="text-[10px] font-bold text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">Active</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500">Contemporary card structure, balanced compact spacing without empty void.</p>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ ($company->invoice_design_template ?? 'modern') === 'classic' ? 'border-brand-600 bg-brand-50/30' : 'border-slate-200' }}">
                        <input type="radio" name="invoice_design_template" value="classic" class="sr-only" {{ ($company->invoice_design_template ?? 'modern') === 'classic' ? 'checked' : '' }}>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs sm:text-sm text-slate-900">📋 Classic Corporate</span>
                            @if(($company->invoice_design_template ?? 'modern') === 'classic')
                            <span class="text-[10px] font-bold text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">Active</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500">Crisp monochrome border grid, traditional corporate Tally layout, compact footer.</p>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ ($company->invoice_design_template ?? 'modern') === 'greenstudio' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-200' }}">
                        <input type="radio" name="invoice_design_template" value="greenstudio" class="sr-only" {{ ($company->invoice_design_template ?? 'modern') === 'greenstudio' ? 'checked' : '' }}>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs sm:text-sm text-slate-900">🌿 Green Studio</span>
                            @if(($company->invoice_design_template ?? 'modern') === 'greenstudio')
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Active</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500">Hosting & Digital layout with diagonal PAID ribbon, clean meta box, and transaction history.</p>
                    </label>
                </div>
            </div>

            <!-- Print Visibility Toggles -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Print Visibility & Section Toggles</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="show_bank_on_invoice" value="1" {{ ($company->show_bank_on_invoice ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 mt-0.5">
                        <div>
                            <strong class="text-xs text-slate-900 block">Show Bank Details on Invoices</strong>
                            <span class="text-[11px] text-slate-500">Uncheck to hide bank name and A/C number.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="show_qr_on_invoice" value="1" {{ ($company->show_qr_on_invoice ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 mt-0.5">
                        <div>
                            <strong class="text-xs text-slate-900 block">Show Payment QR Code on Invoices</strong>
                            <span class="text-[11px] text-slate-500">Uncheck to hide the dynamic UPI QR code.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Vertical Presets -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Industry Vertical Specialized Presets</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ ($company->invoice_template ?? 'standard') === 'standard' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200' }}">
                        <input type="radio" name="invoice_template" value="standard" class="w-4 h-4 text-brand-600" {{ ($company->invoice_template ?? 'standard') === 'standard' ? 'checked' : '' }}>
                        <div>
                            <span class="font-bold text-slate-900 text-xs sm:text-sm block">Standard Trading & Goods</span>
                            <span class="text-[11px] text-slate-500">Standard HSN codes, pieces, kilograms.</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ ($company->invoice_template ?? 'standard') === 'hosting_domain' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200' }}">
                        <input type="radio" name="invoice_template" value="hosting_domain" class="w-4 h-4 text-brand-600" {{ ($company->invoice_template ?? 'standard') === 'hosting_domain' ? 'checked' : '' }}>
                        <div>
                            <span class="font-bold text-slate-900 text-xs sm:text-sm block">Web Hosting & Domain Registrar</span>
                            <span class="text-[11px] text-slate-500">Adds Domain Names & Service Subscription Periods.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- TAB 3: PAYMENTS, UPI QR & RAZORPAY -->
        <div x-show="activeTab === 'payments'" class="space-y-5" x-cloak>
            <!-- Dynamic UPI QR -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">⚡ Dynamic NPCI UPI QR Code (0% Fee)</h3>
                        <p class="text-xs text-slate-500">Scannable by PhonePe, Google Pay, Paytm, CRED & BHIM.</p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="enable_upi_qr" value="1" {{ $company->enable_upi_qr ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                        <span class="text-xs font-bold text-slate-700">Enable</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Company UPI VPA ID</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $company->upi_id) }}" placeholder="e.g. yourbusiness@okaxis"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Payee Name in Bank</label>
                        <input type="text" name="upi_name" value="{{ old('upi_name', $company->upi_name ?: $company->name) }}" placeholder="Acme Infotech"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Dedicated Razorpay Gateway -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3" x-data="{ showSecret: false }">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">💳 Dedicated Razorpay Online Gateway</h3>
                        <p class="text-xs text-slate-500">Credit/Debit Cards & NetBanking into YOUR account.</p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="enable_razorpay" value="1" {{ $company->enable_razorpay ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                        <span class="text-xs font-bold text-slate-700">Enable</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Razorpay Key ID</label>
                        <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $company->razorpay_key_id) }}" placeholder="rzp_live_xxxxxxxxxxxxxx"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Razorpay Key Secret</label>
                        <div class="relative">
                            <input :type="showSecret ? 'text' : 'password'" name="razorpay_key_secret" 
                                   placeholder="{{ $company->razorpay_key_secret ? '••••••••' : 'Secret Key' }}"
                                   class="w-full px-3 py-2 pr-10 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <button type="button" @click="showSecret = !showSecret" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 text-xs">
                                <span x-show="!showSecret">👁️</span><span x-show="showSecret" x-cloak>🙈</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Wire Details -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Direct Bank Wire (NEFT / RTGS)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $company->bank_name) }}" placeholder="HDFC Bank"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Account Number</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $company->bank_account_number) }}"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">IFSC Code</label>
                        <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $company->bank_ifsc) }}" placeholder="HDFC0000240"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono uppercase text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Branch Name</label>
                        <input type="text" name="bank_branch" value="{{ old('bank_branch', $company->bank_branch) }}" placeholder="Electronic City"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: SMTP & WHATSAPP -->
        <div x-show="activeTab === 'comms'" class="space-y-5" x-cloak>
            <!-- Dedicated SMTP Box -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3" x-data="{ showMailPass: false }">
                <div class="pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">✉️ Dedicated Company SMTP Mail Server</h3>
                    <p class="text-xs text-slate-500">Emails sent directly through YOUR mail server.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">SMTP Host</label>
                        <input type="text" name="mail_host" value="{{ old('mail_host', $company->mail_host) }}" placeholder="smtp.gmail.com or mail.domain.com"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">SMTP Port</label>
                        <input type="number" name="mail_port" value="{{ old('mail_port', $company->mail_port ?: 587) }}" placeholder="587"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Encryption</label>
                        <select name="mail_encryption" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="tls" {{ ($company->mail_encryption ?: 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                            <option value="ssl" {{ $company->mail_encryption === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                            <option value="none" {{ $company->mail_encryption === 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Username / Email</label>
                        <input type="text" name="mail_username" value="{{ old('mail_username', $company->mail_username) }}" placeholder="billing@domain.com"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Password</label>
                        <div class="relative">
                            <input :type="showMailPass ? 'text' : 'password'" name="mail_password" placeholder="{{ $company->mail_password ? '••••••••' : 'App Password' }}"
                                   class="w-full px-3 py-2 pr-10 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <button type="button" @click="showMailPass = !showMailPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 text-xs">
                                <span x-show="!showMailPass">👁️</span><span x-show="showMailPass" x-cloak>🙈</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">"From" Email Address</label>
                        <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $company->mail_from_address ?: $company->email) }}" placeholder="billing@domain.com"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- WhatsApp Template -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900">💬 Dedicated WhatsApp Business Integration</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">WhatsApp Phone Number</label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $company->whatsapp_number) }}" placeholder="+91 9876543210"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Default WhatsApp Message Template</label>
                        <textarea name="whatsapp_template" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('whatsapp_template', $company->whatsapp_template) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <!-- LIVE SMTP TEST COMPONENT -->
    @if($company->mail_host)
    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h4 class="font-bold text-xs sm:text-sm text-slate-900">⚡ Test Dedicated SMTP Connection</h4>
            <p class="text-[11px] text-slate-500">Send a live test message through {{ $company->mail_host }} to confirm outgoing delivery.</p>
        </div>
        <form action="{{ route('settings.test_mail') }}" method="POST" class="flex items-center gap-2">
            @csrf
            <input type="email" name="test_email" value="{{ auth()->user()->email }}" required
                   class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-mono">
            <button type="submit" class="px-4 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors">
                Send Test Mail
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
