<x-app-layout header="Executive Billing Dashboard">
    <div class="space-y-6">
        
        <!-- Welcome Hero Banner -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-brand-500/20 text-brand-300 text-xs font-mono font-medium border border-brand-500/30">
                            {{ $company->name }}
                        </span>
                        <span class="text-xs text-slate-400">· GSTIN: {{ $company->gstin ?: 'Not Configured' }}</span>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight">Welcome back, {{ auth()->user()->name }}!</h2>
                    <p class="text-sm text-slate-300 mt-1">Multi-Tenant ERP billing engine is active. Current Tax Mode: <span class="font-semibold text-brand-300 uppercase">{{ $company->tax_mode === 'detailed' ? 'Detailed Split (CGST+SGST/IGST)' : 'Simple GST (18%)' }}</span></p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold shadow-lg shadow-brand-500/30 transition-all">
                        + Create Invoice
                    </a>
                    <a href="{{ route('settings.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold border border-slate-700 transition-all">
                        Tax & Prefix Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Billed</span>
                <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono mt-2">
                    ₹{{ number_format($totalRevenue, 2) }}
                </div>
                <div class="text-xs text-emerald-600 mt-1 font-medium">
                    {{ $invoicesCount }} Total Invoices Issued
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Collected (Paid)</span>
                <div class="text-2xl font-extrabold text-emerald-700 tracking-tight font-mono mt-2">
                    ₹{{ number_format($paidAmount, 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Settled into Bank / UPI
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pending Dues</span>
                <div class="text-2xl font-extrabold text-amber-700 tracking-tight font-mono mt-2">
                    ₹{{ number_format($unpaidAmount, 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Awaiting customer clearance
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">GST Collected</span>
                <div class="text-2xl font-extrabold text-purple-700 tracking-tight font-mono mt-2">
                    ₹{{ number_format($totalTaxes, 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    {{ $company->tax_mode === 'detailed' ? 'Split CGST/SGST/IGST' : 'Total 18% GST collected' }}
                </div>
            </div>
        </div>

        <!-- Recent Invoices Table & Activity Stream -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900">Recent Invoices</h3>
                        <p class="text-xs text-slate-500">Real-time tenant ledger</p>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-800">View All Invoices &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">Invoice #</th>
                                <th class="py-3 px-4">Customer</th>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4 text-right">Amount</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentInvoices as $inv)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-brand-700">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-900">{{ $inv->customer->name ?? 'Direct' }}</div>
                                    <div class="text-xs text-slate-400">{{ $inv->customer->phone ?? '' }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-xs">
                                    {{ $inv->invoice_date->format('d M, Y') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    ₹{{ number_format($inv->total_amount, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($inv->status === 'unpaid' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600') }}">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" title="Print" class="text-xs font-semibold px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700">
                                            Print
                                        </a>
                                        <a href="{{ route('invoices.show', $inv->id) }}" title="View" class="text-xs font-semibold px-2 py-1 bg-brand-50 hover:bg-brand-100 rounded text-brand-700">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                    No invoices generated yet. Click "+ New Invoice" to start billing!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Audit Trail & Activity</h3>
                    <p class="text-xs text-slate-500 mb-4">Immutable company activity log</p>

                    <div class="space-y-3">
                        @forelse($recentLogs as $log)
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-2 h-2 rounded-full bg-brand-500 mt-1 flex-shrink-0"></div>
                            <div>
                                <div class="font-semibold text-slate-800">{{ $log->description }}</div>
                                <div class="text-slate-400 text-[11px] mt-0.5">
                                    By {{ $log->user_name }} · {{ $log->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-xs text-slate-400">No activity logged yet.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <span class="font-bold text-slate-800 block">Custom Invoice Numbering</span>
                        <p class="text-slate-500 mt-1">Want custom prefix or want to freely edit invoice numbers manually? Visit Settings.</p>
                        <a href="{{ route('settings.index') }}" class="inline-block mt-2 font-bold text-brand-600 hover:text-brand-700">
                            Configure Settings &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
