<x-app-layout header="Global SaaS Platform Control Center">
    <div class="space-y-6" x-data="{ currentTab: '{{ request('tab', 'directory') }}' }">
        
        <!-- Super Admin Hero -->
        <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white shadow-2xl relative overflow-hidden border border-slate-800">
            <div class="absolute -right-10 -bottom-10 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-0 right-1/4 w-64 h-64 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-mono font-black tracking-wider uppercase border border-rose-500/30 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                            SUPER ADMIN GOVERNANCE
                        </span>
                        <span class="text-xs text-slate-400 font-medium">· Multi-Tenant Platform Controller</span>
                        @if($requireApproval)
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[11px] font-bold border border-amber-500/30">
                                🛡️ Manual Verification Policy Active
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-bold border border-emerald-500/30">
                                ⚡ Instant Auto-Onboard Active
                            </span>
                        @endif
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-white">Platform Master Control Hub</h1>
                    <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                        Master administrative control across all registered GST billing tenants, verification pipelines, security authorization queues, and global activity audit trails.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if($pendingApprovalsCount > 0)
                        <button @click="currentTab = 'approvals'" class="px-4 py-2.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 border border-amber-500/40 text-xs font-bold transition-all flex items-center gap-2 shadow-lg">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            {{ $pendingApprovalsCount }} Pending Approvals
                        </button>
                    @endif
                    <button type="button" @click="showOnboardModal = true" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                        <span>+</span> Onboard New Company
                    </button>
                </div>
            </div>

            <!-- Platform Top Metrics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-slate-800/80">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Companies</div>
                    <div class="text-2xl font-black text-white font-mono mt-1">{{ $totalCompanies }}</div>
                    <div class="text-xs text-emerald-400 font-medium mt-0.5">{{ $activeCompanies }} Active · {{ $pendingCompaniesCount }} Pending</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Platform GMV</div>
                    <div class="text-2xl font-black text-emerald-400 font-mono mt-1">₹{{ number_format($totalGrossVolume, 2) }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Across all tenant invoices</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Invoices Issued</div>
                    <div class="text-2xl font-black text-indigo-300 font-mono mt-1">{{ $totalInvoices }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">GST bills generated</div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Platform Users</div>
                    <div class="text-2xl font-black text-purple-300 font-mono mt-1">{{ $totalUsers }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Company admins & staff</div>
                </div>
            </div>
        </div>

        <!-- Super Admin Tabs Navigation -->
        <div class="flex items-center gap-2 border-b border-slate-200 bg-white px-4 pt-2 rounded-2xl shadow-sm overflow-x-auto">
            <button @click="currentTab = 'directory'" 
                    :class="currentTab === 'directory' ? 'text-brand-600 border-brand-600 bg-brand-50/50' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                <span>🏢</span>
                <span>Tenant Directory ({{ $totalCompanies }})</span>
            </button>

            <button @click="currentTab = 'approvals'" 
                    :class="currentTab === 'approvals' ? 'text-amber-600 border-amber-600 bg-amber-50/50' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 rounded-t-xl relative">
                <span>🛡️</span>
                <span>Approvals & Security Queue</span>
                @if($pendingApprovalsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">
                        {{ $pendingApprovalsCount }}
                    </span>
                @endif
            </button>

            <button @click="currentTab = 'audit'" 
                    :class="currentTab === 'audit' ? 'text-indigo-600 border-indigo-600 bg-indigo-50/50' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                <span>📜</span>
                <span>Global Audit Logs</span>
            </button>

            <button @click="currentTab = 'mail'" 
                    :class="currentTab === 'mail' ? 'text-emerald-600 border-emerald-600 bg-emerald-50/50' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                <span>📧</span>
                <span>System Auth Mail & OTP Server</span>
                @if($platformMail['is_configured'])
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @else
                    <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[9px] font-bold">SETUP</span>
                @endif
            </button>

            <button @click="currentTab = 'policies'" 
                    :class="currentTab === 'policies' ? 'text-purple-600 border-purple-600 bg-purple-50/50' : 'text-slate-600 border-transparent hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                <span>⚙️</span>
                <span>Platform Policies</span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: TENANT COMPANIES DIRECTORY           -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'directory'" class="space-y-4">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
                <form action="{{ route('superadmin.index') }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <input type="hidden" name="tab" value="directory">
                    <div class="relative w-full sm:w-96">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company name, GSTIN, or email..."
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">🔍</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(request('search'))
                            <a href="{{ route('superadmin.index', ['tab' => 'directory']) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:bg-slate-100">
                                Clear Search
                            </a>
                        @endif
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Company Name & GSTIN</th>
                                <th class="py-3.5 px-4">Primary Admin</th>
                                <th class="py-3.5 px-4">Tax Mode</th>
                                <th class="py-3.5 px-4">Template</th>
                                <th class="py-3.5 px-4">SMTP Server</th>
                                <th class="py-3.5 px-4 text-center">Invoices</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($companies as $comp)
                            @php $owner = $comp->users->first(); @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900">{{ $comp->name }}</div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5">
                                        GSTIN: <span class="text-slate-800 font-semibold">{{ $comp->gstin ?: 'Not configured' }}</span>
                                        @if($comp->city || $comp->state)
                                            · {{ $comp->city }}, {{ $comp->state }}
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Registered: {{ $comp->created_at->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-xs">
                                    <div class="font-semibold text-slate-800">{{ $owner->name ?? 'No Admin' }}</div>
                                    <div class="text-slate-500 font-mono">{{ $owner->email ?? $comp->email }}</div>
                                    @if($owner?->phone)
                                        <div class="text-slate-400">{{ $owner->phone }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded {{ $comp->tax_mode === 'detailed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $comp->tax_mode === 'detailed' ? 'CGST/SGST Split' : 'Simple 18%' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-xs">
                                    <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded {{ $comp->invoice_template === 'hosting_domain' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $comp->invoice_template === 'hosting_domain' ? '🌐 Hosting' : 'Standard' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-mono text-xs text-slate-600">
                                    @if($comp->mail_host)
                                        <span class="text-emerald-600 font-semibold" title="{{ $comp->mail_host }}">✓ Dedicated</span>
                                    @else
                                        <span class="text-slate-400">Platform SMTP</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center font-mono font-bold text-slate-700">
                                    {{ $comp->invoices_count }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($comp->approval_status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            ⏳ Pending
                                        </span>
                                    @elseif($comp->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspended
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($comp->is_active && $comp->approval_status !== 'pending')
                                            <form action="{{ route('superadmin.impersonate', $comp->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" title="Login directly into this tenant company"
                                                        class="px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold border border-brand-200 transition-all flex items-center gap-1.5">
                                                    <span>⚡</span> Impersonate
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('superadmin.toggle_status', $comp->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('Change status for {{ $comp->name }}?')"
                                                    class="px-3 py-1.5 rounded-xl text-xs font-bold border transition-all {{ $comp->is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                                                {{ $comp->is_active ? 'Suspend' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    No tenant companies found matching your query.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($companies->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $companies->appends(['tab' => 'directory', 'search' => request('search')])->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: APPROVALS & SECURITY QUEUE           -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'approvals'" class="space-y-6">
            
            <!-- Section A: Pending Company Registrations -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <span>🏢 Company Onboarding Approvals</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                {{ $pendingCompanies->count() }} Pending
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Companies awaiting Super Admin verification of GSTIN & legitimate business credentials.</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($pendingCompanies as $pending)
                    @php $pAdmin = $pending->users->first(); @endphp
                    <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900 text-base">{{ $pending->name }}</h4>
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 text-slate-700">
                                    GSTIN: {{ $pending->gstin ?: 'Not Provided' }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 flex flex-wrap items-center gap-3">
                                <span><strong>Admin:</strong> {{ $pAdmin->name ?? 'N/A' }} ({{ $pAdmin->email ?? $pending->email }})</span>
                                @if($pAdmin?->phone)
                                    <span>· <strong>Phone:</strong> {{ $pAdmin->phone }}</span>
                                @endif
                                <span>· <strong>Submitted:</strong> {{ $pending->created_at->diffForHumans() }}</span>
                            </div>
                            @if($pending->address)
                                <div class="text-xs text-slate-400">
                                    {{ $pending->address }}, {{ $pending->city }}, {{ $pending->state }} - {{ $pending->pincode }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('superadmin.approve_company', $pending->id) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Approve and activate {{ $pending->name }}?')"
                                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5">
                                    <span>✓</span> Verify & Approve
                                </button>
                            </form>
                            <form action="{{ route('superadmin.reject_company', $pending->id) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Reject registration for {{ $pending->name }}?')"
                                        class="px-4 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition-all">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        ✓ All company onboarding registrations are verified! No pending company reviews.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Section B: Registered Email Change Requests -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <span>🔒 Registered Email Change Requests</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                                {{ $emailRequests->where('status', 'pending')->count() }} Pending Review
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tenant companies requesting updates to their official billing login email (Anti-Takeover Protected).</p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($emailRequests as $req)
                    <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900">{{ $req->company->name ?? 'Unknown Company' }}</h4>
                                <span class="text-xs text-slate-400">· Submitted by {{ $req->user->name ?? 'User' }}</span>
                                @if($req->status === 'pending')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending Review</span>
                                @elseif($req->status === 'approved')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Rejected</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600 line-through">{{ $req->current_email }}</span>
                                <span class="text-slate-400">&rarr;</span>
                                <span class="font-mono bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-bold">{{ $req->requested_email }}</span>
                                <span class="text-slate-400">({{ $req->created_at->format('d M Y, h:i A') }})</span>
                            </div>

                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs text-slate-700 max-w-2xl">
                                <strong class="font-semibold text-slate-900">Reason:</strong> {{ $req->reason }}
                            </div>
                        </div>

                        @if($req->status === 'pending')
                            <div class="flex items-center gap-2">
                                <form action="{{ route('superadmin.approve_email_change', $req->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Approve email change to {{ $req->requested_email }}?')"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5">
                                        <span>✓</span> Approve Change
                                    </button>
                                </form>
                                <form action="{{ route('superadmin.reject_email_change', $req->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Reject this email change request?')"
                                            class="px-4 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition-all">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-xs text-slate-400 italic">
                                Actioned on {{ $req->actioned_at?->format('d M Y') }}
                            </div>
                        @endif
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        No email change requests on file.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: GLOBAL AUDIT LOGS                    -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'audit'" class="space-y-6">
            <!-- Live Active & Online Platform Users -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-900">Live Online & Active Platform Users</h3>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Real-time presence tracking across Super Admins, Company Admins, and Staff members.</p>
                    </div>
                    <span class="text-xs font-mono font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $onlineUsers->count() }} Currently Active
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($onlineUsers as $ou)
                        <div class="p-3.5 rounded-2xl border border-emerald-200/80 bg-emerald-50/20 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white font-bold flex items-center justify-center text-xs">
                                        {{ substr($ou->name, 0, 1) }}
                                    </div>
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white"></span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>{{ $ou->name }}</span>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded font-mono font-bold uppercase
                                            {{ $ou->role === 'super_admin' ? 'bg-rose-100 text-rose-700' : ($ou->role === 'company_admin' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                            {{ str_replace('_', ' ', $ou->role) }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 truncate max-w-[180px]">
                                        {{ $ou->company->name ?? 'Platform Governance' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        IP: {{ $ou->last_ip ?? '127.0.0.1' }} · {{ $ou->last_seen_at ? $ou->last_seen_at->diffForHumans() : 'Active now' }}
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">ONLINE</span>
                        </div>
                    @empty
                        <div class="col-span-full p-4 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-2xl">
                            No active users recorded in the last 5 minutes.
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900">Cross-Tenant Global Audit Trail</h3>
                        <p class="text-xs text-slate-500">Immutable chronological log of sensitive actions across all companies, logins, and billing operations.</p>
                    </div>
                    <span class="text-xs font-mono text-slate-400">Displaying last 50 events</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                    <div class="p-4 sm:px-6 flex items-start justify-between gap-4 hover:bg-slate-50/80 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold mt-0.5 flex-shrink-0
                                {{ str_contains($log->action, 'approval') ? 'bg-emerald-100 text-emerald-700' :
                                  (str_contains($log->action, 'security') ? 'bg-rose-100 text-rose-700' :
                                  (str_contains($log->action, 'payment') ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700')) }}">
                                {{ substr(strtoupper($log->action), 0, 2) }}
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-xs font-bold text-slate-900">
                                    {{ $log->description }}
                                </div>
                                <div class="text-[11px] text-slate-500 flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-700">By: {{ $log->user_name ?? 'System' }}</span>
                                    <span>· Role: {{ $log->role }}</span>
                                    @if($log->company_id)
                                        <span>· Tenant ID #{{ $log->company_id }}</span>
                                    @else
                                        <span class="text-indigo-600 font-bold">· Platform Level</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 text-xs text-slate-400 font-mono">
                            {{ $log->created_at->format('d M, h:i A') }}
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        No audit events recorded yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: PLATFORM POLICIES & SECURITY         -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'policies'" class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Platform Governance Policies</h3>
                    <p class="text-xs text-slate-500">Configure multi-tenant access boundaries, verification gates, and platform-wide defaults.</p>
                </div>

                <!-- Onboarding Policy Setting -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-sm text-slate-900">Company Onboarding Verification Gate</h4>
                            @if($requireApproval)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Active: Manual Approval</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Active: Auto-Activate</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 max-w-xl">
                            When enabled, newly registered companies cannot create invoices or access tenant portals until Super Admin reviews and approves their GSTIN and registration details.
                        </p>
                    </div>

                    <form action="{{ route('superadmin.toggle_onboarding_policy') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all {{ $requireApproval ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white' }}">
                            {{ $requireApproval ? 'Switch to Instant Auto-Activate' : 'Require Admin Approval for All' }}
                        </button>
                    </form>
                </div>

                <!-- Security Standard Matrix -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white">
                        <div class="text-base mb-1">🔐</div>
                        <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Tenant Data Isolation</h5>
                        <p class="text-xs text-slate-500 mt-1">
                            Enforced via global `TenantScope` and strict `EnsureTenant` middleware. Cross-tenant leakage is cryptographically prevented.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white">
                        <div class="text-base mb-1">🛡️</div>
                        <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Email Anti-Takeover</h5>
                        <p class="text-xs text-slate-500 mt-1">
                            Tenant administrators cannot change their official registered billing email without submitting an approval request to Super Admin.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white">
                        <div class="text-base mb-1">⚡</div>
                        <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Super Admin Protection</h5>
                        <p class="text-xs text-slate-500 mt-1">
                            Super Admin sessions cannot inadvertently create tenant invoices without active company impersonation, preventing null object crashes.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: SYSTEM AUTH MAIL & OTP SERVER       -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'mail'" class="space-y-6">
            <!-- Header Banner -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900">Platform System Mail & Authentication SMTP</h3>
                            @if($platformMail['is_configured'])
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active Dedicated SMTP
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Fallback: System .env Mailer
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 max-w-2xl">
                            All platform-level authentication emails—including <strong>Super Admin & User 2FA OTP codes</strong>, <strong>Company Onboarding 4-digit verification OTPs</strong>, and <strong>Forgot Password resets</strong>—are delivered through this central SMTP server.
                        </p>
                    </div>

                    <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs max-w-sm">
                        <span class="font-bold">💡 Multi-Tenant Isolation Rule:</span> Tenant companies configure their own custom SMTP under their workspace <em>Settings &gt; Mail Settings</em> strictly to dispatch customer invoices with their brand name.
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- SMTP Configuration Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h4 class="font-bold text-slate-900">System SMTP Credentials</h4>
                        <p class="text-xs text-slate-500">Configure connection details for sending system alerts and authentication emails.</p>
                    </div>

                    <form action="{{ route('superadmin.mail.update') }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2 space-y-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">SMTP Host *</label>
                                <input type="text" name="platform_mail_host" value="{{ old('platform_mail_host', $platformMail['host']) }}" required placeholder="smtp.gmail.com / mail.domain.com"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-mono">
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Port *</label>
                                <input type="number" name="platform_mail_port" value="{{ old('platform_mail_port', $platformMail['port']) }}" required placeholder="587 / 465"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">SMTP Username *</label>
                                <input type="text" name="platform_mail_username" value="{{ old('platform_mail_username', $platformMail['username']) }}" required placeholder="auth@domain.com"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-mono">
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">SMTP Password / App Key *</label>
                                <input type="password" name="platform_mail_password" value="{{ old('platform_mail_password', $platformMail['password']) }}" placeholder="••••••••••••••••"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Encryption Protocol</label>
                                <select name="platform_mail_encryption" class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="tls" {{ $platformMail['encryption'] === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                    <option value="ssl" {{ $platformMail['encryption'] === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="none" {{ $platformMail['encryption'] === 'none' ? 'selected' : '' }}>None (Local / Dev)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">From Email Address *</label>
                                <input type="email" name="platform_mail_from_address" value="{{ old('platform_mail_from_address', $platformMail['from_address']) }}" required placeholder="auth@domain.com"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-mono">
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">From Sender Name *</label>
                                <input type="text" name="platform_mail_from_name" value="{{ old('platform_mail_from_name', $platformMail['from_name']) }}" required placeholder="GST SaaS Auth Portal"
                                       class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                                <span>💾</span>
                                <span>Save System Mail Settings</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Test Diagnostic Email & Security Note (1 col) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🧪</span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Send Diagnostic Test Email</h4>
                                <p class="text-xs text-slate-500">Verify SMTP handshake & authentication delivery</p>
                            </div>
                        </div>

                        <form action="{{ route('superadmin.mail.test') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-xs font-bold text-slate-700">Recipient Email Address</label>
                                <input type="email" name="test_email" value="{{ auth()->user()->email }}" required placeholder="youremail@domain.com"
                                       class="w-full mt-1 text-xs font-semibold px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md transition-all flex items-center justify-center gap-2">
                                <span>⚡</span>
                                <span>Send Test Email Now</span>
                            </button>
                        </form>
                    </div>

                    <div class="bg-slate-900 text-white rounded-3xl p-6 space-y-3">
                        <div class="text-amber-400 font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <span>🛡️</span> Security & Architecture
                        </div>
                        <h5 class="font-bold text-sm text-white">Centralized Platform Auth</h5>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            By decoupling Platform Auth SMTP from tenant-specific mail accounts, tenant companies cannot intercept password reset tokens, OTP codes, or superadmin notifications.
                        </p>
                        <div class="text-[11px] text-slate-400 border-t border-slate-800 pt-3">
                            Fallback: If dedicated SMTP is not filled, system defaults to <code>MAIL_MAILER</code> from your server <code>.env</code>.
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</x-app-layout>
