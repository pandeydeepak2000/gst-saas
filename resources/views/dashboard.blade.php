<x-app-layout header="Executive Billing Dashboard">
    <div class="space-y-6">
        
        <!-- Welcome Executive Hero Banner -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-80 h-80 bg-gradient-to-br from-brand-100/40 via-indigo-100/30 to-transparent rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-mono font-bold border border-brand-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                            {{ $company?->name ?? 'GST-SaaS Platform' }}
                        </span>
                        @if($company?->gstin)
                        <span class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                            GSTIN: {{ $company->gstin }}
                        </span>
                        @endif
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase {{ ($company?->tax_mode ?? 'simple') === 'detailed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ ($company?->tax_mode ?? 'simple') === 'detailed' ? 'Split CGST/SGST Mode' : 'Simple 18% GST Mode' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Good day, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-xl">
                        Here is your business financial overview, client receivables, and recent tax invoices.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('invoices.create') }}" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-brand-500/20 transition-all flex items-center gap-1.5">
                        <span>+ Create Invoice</span>
                    </a>
                    <a href="{{ route('reports.gstr1') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs sm:text-sm font-bold border border-slate-200 transition-all flex items-center gap-1.5">
                        <span>📊 GSTR-1 Reports</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="p-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 border border-slate-200 shadow-sm transition-all" title="Settings">
                        ⚙️
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Premium Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- 1. TOTAL INVOICED -->
            <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Billed</span>
                    <span class="p-2 rounded-xl bg-brand-50 text-brand-600 text-sm">📈</span>
                </div>
                <div class="text-2xl font-black font-mono text-slate-900 mt-2">
                    ₹{{ number_format($totalRevenue, 2) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-1">
                    <span class="font-bold text-slate-700">{{ $invoicesCount }}</span> total generated bills
                </div>
            </div>

            <!-- 2. PAID AMOUNT -->
            <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Settled & Paid</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 text-sm">✅</span>
                </div>
                <div class="text-2xl font-black font-mono text-emerald-600 mt-2">
                    ₹{{ number_format($paidAmount, 2) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-emerald-700 font-semibold mt-1">
                    Realized into bank accounts
                </div>
            </div>

            <!-- 3. UNPAID BALANCE -->
            <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Receivables Due</span>
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-600 text-sm">⏳</span>
                </div>
                <div class="text-2xl font-black font-mono text-amber-600 mt-2">
                    ₹{{ number_format($unpaidAmount, 2) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-amber-700 font-semibold mt-1">
                    Outstanding from clients
                </div>
            </div>

            <!-- 4. TAX COLLECTED -->
            <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">GST Collected</span>
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 text-sm">🏛️</span>
                </div>
                <div class="text-2xl font-black font-mono text-indigo-600 mt-2">
                    ₹{{ number_format($totalTaxes, 2) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-1">
                    Ready for GSTR-1 filing
                </div>
            </div>

        </div>

        <!-- 2-COLUMN MAIN CONTENT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- RECENT INVOICES TABLE (2 COLS) -->
            <div class="lg:col-span-2 p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Recent Invoices</h3>
                        <p class="text-xs text-slate-500">Latest outward billing and client payments.</p>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-800 hover:underline">
                        View All Invoices →
                    </a>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-3.5">Invoice #</th>
                                <th class="py-3 px-3">Client</th>
                                <th class="py-3 px-3">Date</th>
                                <th class="py-3 px-3 text-right">Amount</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentInvoices as $inv)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-3.5 font-mono font-bold text-brand-700">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">
                                        #{{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    {{ $inv->customer->name ?? 'Walk-in' }}
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-500">
                                    {{ $inv->invoice_date->format('d M') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                    ₹{{ number_format($inv->total_amount, 2) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($inv->status === 'unpaid' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-right">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition-colors">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    No invoices generated yet. Click <strong>+ Create Invoice</strong> to get started.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INTEGRATIONS & ACTIVITY SIDEBAR (1 COL) -->
            <div class="space-y-6">
                
                <!-- INTEGRATION HEALTH STATUS -->
                <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center justify-between">
                        <span>Integrations Status</span>
                        <a href="{{ route('settings.index') }}" class="text-xs text-brand-600 font-semibold hover:underline">Manage</a>
                    </h3>

                    <div class="space-y-2.5 text-xs">
                        <!-- UPI QR -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span>⚡</span>
                                <div>
                                    <strong class="text-slate-800 block">Dynamic UPI QR</strong>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ $company?->upi_id ?: 'Not Configured' }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $company?->enable_upi_qr ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $company?->enable_upi_qr ? 'Active' : 'Disabled' }}
                            </span>
                        </div>

                        <!-- Razorpay -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span>💳</span>
                                <div>
                                    <strong class="text-slate-800 block">Razorpay Gateway</strong>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ $company?->razorpay_key_id ? 'Key Configured' : 'No Key' }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $company?->enable_razorpay ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $company?->enable_razorpay ? 'Active' : 'Disabled' }}
                            </span>
                        </div>

                        <!-- Dedicated SMTP -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span>✉️</span>
                                <div>
                                    <strong class="text-slate-800 block">Dedicated Mail Server</strong>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ $company?->mail_host ?: 'System Mailer' }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $company?->mail_host ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $company?->mail_host ? 'Connected' : 'Default' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- RECENT AUDIT LOGS -->
                <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                    <h3 class="font-bold text-slate-900 text-sm">Recent Audit Log</h3>
                    <div class="space-y-2.5 text-xs">
                        @forelse($recentLogs as $log)
                        <div class="flex items-start gap-2 text-slate-600 pb-2 border-b border-slate-100 last:border-0 last:pb-0">
                            <span class="text-brand-600 font-bold">•</span>
                            <div class="flex-1">
                                <p class="text-slate-800">{{ $log->description }}</p>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400">No activity logs recorded yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>