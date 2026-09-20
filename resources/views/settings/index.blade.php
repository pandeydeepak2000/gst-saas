<x-app-layout header="Company Settings & Billing Preferences">
    <div class="max-w-5xl mx-auto space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Tenant Billing Configuration</h2>
                <p class="text-sm text-slate-500">Configure your company identity, tax display preference, and custom invoice numbering rules.</p>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- 1. TAX DISPLAY PREFERENCE TOGGLE -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <span>Tax Display Mode (GST Toggle)</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-brand-50 text-brand-700 border border-brand-200">Company Default</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Choose how GST should be displayed on your invoice summaries and printed bills.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $company->tax_mode === 'simple' ? 'border-brand-600 bg-brand-50/30' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-sm text-slate-900">Mode A: Simple GST (Single Line)</span>
                            <input type="radio" name="tax_mode" value="simple" {{ $company->tax_mode === 'simple' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                        </div>
                        <p class="text-xs text-slate-500 mb-3">Displays tax as a clean single line (e.g. <strong>"GST (18%): ₹2,808.00"</strong>). Hides complicated CGST/SGST/IGST breakdown.</p>
                        <div class="mt-auto p-2.5 rounded-lg bg-white border border-slate-200/80 font-mono text-[11px] text-slate-600 space-y-1">
                            <div class="flex justify-between"><span>Taxable Amount:</span><span>₹15,600.00</span></div>
                            <div class="flex justify-between text-brand-700 font-bold"><span>GST (18%):</span><span>₹2,808.00</span></div>
                            <div class="flex justify-between border-t border-slate-200 pt-1 font-bold text-slate-900"><span>Grand Total:</span><span>₹18,408.00</span></div>
                        </div>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $company->tax_mode === 'detailed' ? 'border-brand-600 bg-brand-50/30' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-sm text-slate-900">Mode B: Detailed Tax Split</span>
                            <input type="radio" name="tax_mode" value="detailed" {{ $company->tax_mode === 'detailed' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                        </div>
                        <p class="text-xs text-slate-500 mb-3">Displays full <strong>CGST + SGST</strong> (or IGST for interstate) with HSN/SAC summary table. Best for corporate B2B clients.</p>
                        <div class="mt-auto p-2.5 rounded-lg bg-white border border-slate-200/80 font-mono text-[11px] text-slate-600 space-y-1">
                            <div class="flex justify-between"><span>Taxable Amount:</span><span>₹15,600.00</span></div>
                            <div class="flex justify-between text-emerald-700"><span>CGST (9%):</span><span>₹1,404.00</span></div>
                            <div class="flex justify-between text-emerald-700"><span>SGST (9%):</span><span>₹1,404.00</span></div>
                            <div class="flex justify-between border-t border-slate-200 pt-1 font-bold text-slate-900"><span>Grand Total:</span><span>₹18,408.00</span></div>
                        </div>
                    </label>
                </div>
            </div>
                        <!-- 1.5 INVOICE TEMPLATE & INDUSTRY PRESET -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <span>Invoice Format & Industry Preset</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">Industry Theme</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Select your business format. Web Hosting & Domain companies get service period dates, domain references, and cloud billing layout.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $company->invoice_template === 'standard' ? 'border-brand-600 bg-brand-50/20' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-sm text-slate-900">1. Standard Wholesale & Services</span>
                            <input type="radio" name="invoice_template" value="standard" {{ $company->invoice_template !== 'hosting_domain' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                        </div>
                        <p class="text-xs text-slate-500">General trading, manufacturing, hardware, retail, consulting, and standard items with HSN/SAC.</p>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $company->invoice_template === 'hosting_domain' ? 'border-brand-600 bg-brand-50/20' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-sm text-slate-900">2. 🌐 Web Hosting, Cloud & Domain Registrar</span>
                            <input type="radio" name="invoice_template" value="hosting_domain" {{ $company->invoice_template === 'hosting_domain' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                        </div>
                        <p class="text-xs text-slate-500">Dedicated domain tags, billing cycles (Annually/Monthly), service subscription period badges, and cloud infrastructure SAC 998315.</p>
                    </label>
                </div>
            </div>

            <!-- 2. CUSTOM INVOICE NUMBERING CONFIGURATION -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div>
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span>Invoice Numbering Engine</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">Customizable</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Define your automated prefix or allow staff to freely type any custom invoice number on billing screens.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Company Invoice Prefix *</label>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $company->invoice_prefix) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                        <p class="text-[11px] text-slate-400 mt-1">e.g. <code>INV/2026/</code>, <code>GS-</code>, <code>KUMAR/</code></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Starting Sequence Number *</label>
                        <input type="number" name="invoice_start_number" value="{{ old('invoice_start_number', $company->invoice_start_number) }}" min="1" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                        <p class="text-[11px] text-slate-400 mt-1">Next auto invoice: <strong>{{ $company->generateNextInvoiceNumber() }}</strong></p>
                    </div>
                </div>

                <!-- Manual Number Edit Permission -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start gap-3">
                    <input type="checkbox" id="allow_manual_invoice_number" name="allow_manual_invoice_number" value="1" {{ $company->allow_manual_invoice_number ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <label for="allow_manual_invoice_number" class="cursor-pointer">
                        <span class="font-bold text-sm text-slate-800 block">Allow Free Manual Invoice Number Editing</span>
                        <span class="text-xs text-slate-500 block mt-0.5">When checked, any user or billing staff can freely override, customize, or type any invoice number format directly during invoice creation or editing.</span>
                    </label>
                </div>
            </div>

            <!-- 3. COMPANY IDENTITY & GSTIN -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900">Company Identity & Location</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Company / Trade Name *</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Official GSTIN</label>
                        <input type="text" name="gstin" value="{{ old('gstin', $company->gstin) }}" placeholder="10AAAAA0000A1Z5"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Business Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Business Email</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Office / Shop Address</label>
                        <textarea name="address" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('address', $company->address) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $company->city) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">State *</label>
                        <input type="text" name="state" value="{{ old('state', $company->state) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 4. BANK DETAILS & UPI ID (FOR INVOICE QR CODE) -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div>
                    <h3 class="font-bold text-slate-900">Bank Details & UPI QR Code</h3>
                    <p class="text-xs text-slate-500 mt-0.5">These will be printed at the bottom of customer invoices for instant QR scanning and payment.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $company->bank_name) }}" placeholder="e.g. HDFC Bank, SBI"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Account Number</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $company->bank_account_number) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">IFSC Code</label>
                        <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $company->bank_ifsc) }}" placeholder="HDFC0000240"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">UPI ID (For Dynamic Payment QR)</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $company->upi_id) }}" placeholder="name@upi"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>

                        <!-- 5. DEDICATED COMPANY SMTP MAIL SERVER SETTINGS -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4" x-data="{ showSmtpPass: false }">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <span>Dedicated Company SMTP Mail Server</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Per-Tenant Emailing</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">When configured, all client invoice notifications and staff password resets will be sent directly through YOUR mail server (e.g. Google Workspace, cPanel, or SendGrid).</p>
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
                            <option value="tls" {{ $company->mail_encryption === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                            <option value="ssl" {{ $company->mail_encryption === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                            <option value="none" {{ $company->mail_encryption === 'none' ? 'selected' : '' }}>None (Port 25)</option>
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
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">From Sender Email</label>
                        <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $company->mail_from_address) }}" placeholder="no-reply@mycompany.com"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-md shadow-brand-600/20 active:scale-[0.99] transition-all">
                    Save Changes & Preferences
                </button>
            </div>
        </form>

        <!-- 6. LIVE SMTP TEST BOX -->
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
                <input type="email" name="test_email" required placeholder="recipient@example.com" value="{{ auth()->user()->email }}"
                       class="px-3 py-2 text-xs rounded-xl border border-slate-300 text-slate-900 w-56 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all whitespace-nowrap">
                    Send Test Email &rarr;
                </button>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>
