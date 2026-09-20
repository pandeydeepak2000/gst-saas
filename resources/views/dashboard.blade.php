<x-app-layout header="Executive Billing Dashboard">
    @php
        $theme = $company?->theme_config ?? [
            'hero_gradient'  => 'from-slate-950 via-slate-900 to-purple-950',
            'accent_badge'   => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
            'button_primary' => 'bg-violet-600 hover:bg-violet-700 text-white',
            'kpi_icon_bg'    => 'bg-purple-50 text-purple-600',
        ];
    @endphp

    <div class="space-y-6">
        
        <!-- Welcome Executive Hero Banner (Dynamic Luxury Gradient) -->
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br {{ $theme['hero_gradient'] }} text-white shadow-xl relative overflow-hidden border border-slate-800">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-80 h-80 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-white/10 text-white text-xs font-mono font-bold border border-white/20 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            {{ $company?->name ?? 'GST-SaaS Platform' }}
                        </span>
                        @if($company?->gstin)
                        <span class="text-xs font-mono font-semibold text-slate-300 bg-white/5 px-2.5 py-0.5 rounded-full border border-white/10">
                            GSTIN: {{ $company->gstin }}
                        </span>
                        @endif
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase {{ ($company?->tax_mode ?? 'simple') === 'detailed' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                            {{ ($company?->tax_mode ?? 'simple') === 'detailed' ? 'Split CGST/SGST Mode' : 'Simple 18% GST Mode' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                        Good day, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">
                        Here is your business financial overview, client receivables, and recent tax invoices.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('invoices.create') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md transition-all flex items-center gap-1.5 {{ $theme['button_primary'] }}">
                        <span>+ Create Invoice</span>
                    </a>
                    <a href="{{ route('reports.gstr1') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-bold border border-white/15 transition-all flex items-center gap-1.5">
                        <span>📊 GSTR-1 Reports</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-all" title="Settings">
                        ⚙️
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Premium Metric KPI Cards (Titanium, Emerald, Amber, Purple) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- 1. TOTAL INVOICED -->
            <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Billed GMV</span>
                    <span class="p-2 rounded-xl bg-slate-100 text-slate-700 text-sm">📈</span>
                </div>
                <div class="text-2xl font-black font-mono text-slate-900 mt-2">
                    ₹{{ number_format($totalRevenue, 2) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-1 font-medium">
                    <span class="font-bold text-slate-800">{{ $invoicesCount }}</span> total invoices generated
                </div>
            </div>

            <!-- 2. PAID AMOUNT -->
            <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Settled & Realized</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 text-sm">✅</span>
                </div>
                <div class="text-2xl font-black font-mono text-emerald-600 mt-2">
                    ₹{{ number_format($paidAmount, 2) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-emerald-700 font-semibold mt-1">
                    Deposited in bank accounts
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
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-700">GST Collected</span>
                    <span class="p-2 rounded-xl bg-purple-50 text-purple-600 text-sm">🏛️</span>
                </div>
                <div class="text-2xl font-black font-mono text-purple-700 mt-2">
                    ₹{{ number_format($totalTaxes, 2) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-purple-600 font-semibold mt-1">
                    Tax liability ready for CA filing
                </div>
            </div>
        </div>

        <!-- Main Content 2-Column Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Recent Invoices (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Recent Invoices</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Latest transactions issued under {{ $company?->name }}</p>
                        </div>
                        <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-slate-700 hover:text-slate-900 underline underline-offset-4">
                            View All ({{ $invoicesCount }}) &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Invoice #</th>
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentInvoices as $inv)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-xs">
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <div class="font-semibold text-slate-800">{{ $inv->customer->name ?? 'Walk-in' }}</div>
                                        @if($inv->customer && $inv->customer->company_name)
                                            <div class="text-slate-400 text-[11px]">{{ $inv->customer->company_name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-500">
                                        {{ $inv->invoice_date->format('d M, Y') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-mono font-bold text-slate-900 text-right">
                                        ₹{{ number_format($inv->total_amount, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded-full uppercase
                                            {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($inv->status === 'partially_paid' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                            {{ $inv->status }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-xs">
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 mr-2">
                                            View
                                        </a>
                                        <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                                            Print
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-slate-400 text-xs">
                                        No invoices created yet. Click "+ Create Invoice" to issue your first bill.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($trashCount > 0)
                <div class="p-3.5 bg-rose-50/50 border-t border-rose-100 flex items-center justify-between text-xs text-rose-800">
                    <span>You have <strong>{{ $trashCount }}</strong> deleted invoice(s) in the trash bin.</span>
                    <a href="{{ route('invoices.index', ['tab' => 'trash']) }}" class="font-bold underline">
                        Open Trash Bin &rarr;
                    </a>
                </div>
                @endif
            </div>

            <!-- Right: Activity Logs & Quick Actions (1 Col) -->
            <div class="space-y-6">
                
                <!-- Quick Navigation Cards -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400">Quick Shortcuts</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('customers.index') }}" class="p-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 transition-all text-left">
                            <span class="text-base">👥</span>
                            <div class="font-bold text-xs text-slate-900 mt-1">Customers</div>
                            <div class="text-[10px] text-slate-500">{{ $customersCount }} Active</div>
                        </a>
                        <a href="{{ route('products.index') }}" class="p-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 transition-all text-left">
                            <span class="text-base">📦</span>
                            <div class="font-bold text-xs text-slate-900 mt-1">Products</div>
                            <div class="text-[10px] text-slate-500">Master Catalog</div>
                        </a>
                    </div>
                </div>

                <!-- Recent Immutable Activity Trail -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400">Security Audit Logs</h4>
                        <span class="text-[10px] text-slate-400 font-mono">Live</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($recentLogs as $log)
                        <div class="flex items-start gap-2.5 text-xs">
                            <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ str_contains($log->action, 'delete') ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                            <div class="space-y-0.5">
                                <div class="font-medium text-slate-800 text-[11px] leading-snug">{{ $log->description }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-slate-400 text-xs text-center py-4">No activity logged yet.</div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>