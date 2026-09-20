<x-app-layout header="Global SaaS Platform Control Center">
    <div class="space-y-6">
        
        <!-- Super Admin Hero -->
        <div class="p-6 rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 text-xs font-mono font-bold border border-rose-500/30">
                            SUPER ADMINISTRATOR
                        </span>
                        <span class="text-xs text-slate-400">· Global SaaS Governance</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight">Platform Master Control</h2>
                    <p class="text-xs text-slate-300 mt-1">Monitor all onboarded companies, tenant health, platform revenue, and manage company access.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('register') }}" target="_blank" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition-all">
                        + Onboard New Company
                    </a>
                </div>
            </div>
        </div>

        <!-- Global Platform KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Onboarded Tenants</span>
                <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono mt-2">
                    {{ $totalCompanies }} Companies
                </div>
                <div class="text-xs text-emerald-600 mt-1 font-medium">
                    {{ $activeCompanies }} Active & Operational
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Billed GMV</span>
                <div class="text-2xl font-extrabold text-brand-700 tracking-tight font-mono mt-2">
                    ₹{{ number_format($totalGrossVolume, 2) }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Gross platform invoicing volume
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Invoices Generated</span>
                <div class="text-2xl font-extrabold text-indigo-700 tracking-tight font-mono mt-2">
                    {{ $totalInvoices }} Invoices
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Issued across all tenant companies
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Platform Users</span>
                <div class="text-2xl font-extrabold text-purple-700 tracking-tight font-mono mt-2">
                    {{ $totalUsers }} Users
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Admins, billing staff & accountants
                </div>
            </div>
        </div>
        <!-- Companies Management Directory -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900">Tenant Companies Register</h3>
                    <p class="text-xs text-slate-500">Full control over tenant access, dedicated SMTP, tax modes, and direct portal access.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Company Name</th>
                            <th class="py-3 px-4">Owner / Admin</th>
                            <th class="py-3 px-4">Tax Mode</th>
                            <th class="py-3 px-4">Template</th>
                            <th class="py-3 px-4">SMTP Server</th>
                            <th class="py-3 px-4 text-center">Invoices</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Super Admin Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($companies as $comp)
                        @php $owner = $comp->users->first(); @endphp
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $comp->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">GSTIN: {{ $comp->gstin ?: 'Not set' }} · {{ $comp->city }}, {{ $comp->state }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-semibold text-slate-800">{{ $owner->name ?? 'No Admin' }}</div>
                                <div class="text-slate-400">{{ $owner->email ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded {{ $comp->tax_mode === 'detailed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $comp->tax_mode === 'detailed' ? 'Split CGST/SGST' : 'Simple 18%' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded {{ $comp->invoice_template === 'hosting_domain' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $comp->invoice_template === 'hosting_domain' ? '🌐 Hosting & Cloud' : 'Standard' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                @if($comp->mail_host)
                                    <span class="text-emerald-600 font-semibold" title="{{ $comp->mail_host }}">✓ Configured</span>
                                @else
                                    <span class="text-slate-400">Default Mailer</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                                {{ $comp->invoices_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $comp->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $comp->is_active ? 'Active' : 'Suspended' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <form action="{{ route('superadmin.impersonate', $comp->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200" title="Log in as this company admin">
                                            Access Portal &rarr;
                                        </button>
                                    </form>

                                    <form action="{{ route('superadmin.toggle_status', $comp->id) }}" method="POST" class="inline" onsubmit="return confirm('Change status for {{ $comp->name }}?');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $comp->is_active ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                            {{ $comp->is_active ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($companies->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $companies->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
