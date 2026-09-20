<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GST-SaaS - Enterprise Multi-Tenant ERP' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800" x-data="{ mobileSidebarOpen: false, showImpersonateModal: false }">
    @php
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin() && !session('impersonator_id');
        $company = $user?->company;
        $theme = $company?->theme_config ?? [
            'primary'        => '#7c3aed',
            'sidebar_active' => 'bg-violet-600 text-white font-semibold shadow-sm',
            'sidebar_hover'  => 'hover:bg-violet-500/10 hover:text-violet-300',
            'button_primary' => 'bg-violet-600 hover:bg-violet-700 text-white shadow-violet-600/20',
            'hero_gradient'  => 'from-slate-950 via-slate-900 to-purple-950',
            'accent_badge'   => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
            'kpi_icon_bg'    => 'bg-purple-50 text-purple-600',
            'ring_focus'     => 'focus:ring-violet-500',
        ];
    @endphp

    <div class="min-h-full flex flex-col lg:flex-row">
        
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" x-cloak 
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
             @click="mobileSidebarOpen = false">
        </div>

        <!-- Sidebar (72 width) -->
        <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 border-r shadow-2xl
               {{ $isSuperAdmin ? 'bg-slate-950 text-slate-300 border-slate-900' : 'bg-slate-900 text-slate-300 border-slate-800' }}">
            
            <!-- Sidebar Top Brand Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b {{ $isSuperAdmin ? 'border-slate-900 bg-black/40' : 'border-slate-800 bg-slate-950/40' }}">
                <a href="{{ $isSuperAdmin ? route('superadmin.index') : route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-lg
                        {{ $isSuperAdmin ? 'bg-gradient-to-tr from-amber-500 to-rose-600 shadow-amber-500/20' : ((($theme['key'] ?? '') === 'emerald') ? 'bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-emerald-500/20' : 'bg-gradient-to-tr from-purple-600 to-cyan-400 shadow-purple-500/20') }}">
                        {{ $isSuperAdmin ? '👑' : strtoupper(substr($company?->name ?? 'G', 0, 1)) }}
                    </div>
                    <div>
                        <span class="font-bold text-lg text-white tracking-tight flex items-center gap-1.5">
                            {{ $isSuperAdmin ? 'Master SaaS' : 'GST-SaaS' }}
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-bold
                                {{ $isSuperAdmin ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : $theme['accent_badge'] }}">
                                {{ $isSuperAdmin ? 'GOVERNOR' : 'PRO' }}
                            </span>
                        </span>
                        <p class="text-xs text-slate-400 truncate max-w-[140px]">
                            {{ $isSuperAdmin ? 'Platform Control' : ($company?->name ?? 'Tenant Workspace') }}
                        </p>
                    </div>
                </a>
                <button @click="mobileSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">&times;</button>
            </div>

            @if(!$isSuperAdmin && $company)
            <!-- Active Tenant Profile Card -->
            <div class="p-4 mx-4 mt-4 rounded-2xl bg-slate-800/60 border border-slate-700/50">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Active Tenant</span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $theme['accent_badge'] }}">
                        {{ $company->tax_mode === 'detailed' ? 'Split CGST/SGST' : 'Simple 18%' }}
                    </span>
                </div>
                <div class="font-bold text-xs text-white truncate">{{ $company->name }}</div>
                <div class="text-[11px] font-mono text-slate-400 mt-0.5 truncate">
                    GSTIN: {{ $company->gstin ?: 'Not Configured' }}
                </div>
            </div>
            @endif

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                
                @if($isSuperAdmin)
                    <!-- ============================================== -->
                    <!-- 1. DEDICATED SUPER ADMIN GOVERNANCE NAVIGATION -->
                    <!-- ============================================== -->
                    <div class="px-3 pb-2 text-[10px] font-mono font-black uppercase tracking-wider text-amber-400/90 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
                        Platform Governance
                    </div>

                    <a href="{{ route('superadmin.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('superadmin.index') && !request()->has('tab') ? 'bg-amber-500 text-slate-950 shadow-md font-black' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span>📊</span>
                        <span>Global Control Center</span>
                    </a>

                    <a href="{{ route('superadmin.index', ['tab' => 'directory']) }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->get('tab') === 'directory' ? 'bg-amber-500 text-slate-950 shadow-md font-black' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <span>🏢</span>
                            <span>Tenant Companies</span>
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono">
                            {{ \App\Models\Company::count() }}
                        </span>
                    </a>

                    <a href="{{ route('superadmin.index', ['tab' => 'approvals']) }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->get('tab') === 'approvals' ? 'bg-amber-500 text-slate-950 shadow-md font-black' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <span>🛡️</span>
                            <span>Approvals & Queue</span>
                        </span>
                        @php 
                            $pendingTotal = \App\Models\Company::where('approval_status', 'pending')->count() + \App\Models\EmailChangeRequest::where('status', 'pending')->count();
                        @endphp
                        @if($pendingTotal > 0)
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-500 text-white font-mono font-bold animate-pulse">
                                {{ $pendingTotal }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('superadmin.index', ['tab' => 'audit']) }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->get('tab') === 'audit' ? 'bg-amber-500 text-slate-950 shadow-md font-black' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span>📜</span>
                        <span>Global Audit Trail</span>
                    </a>

                    <a href="{{ route('superadmin.index', ['tab' => 'policies']) }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->get('tab') === 'policies' ? 'bg-amber-500 text-slate-950 shadow-md font-black' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span>⚙️</span>
                        <span>Platform Policies</span>
                    </a>

                    <div class="pt-4 mt-4 border-t border-slate-900">
                        <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Security</span>
                    </div>

                    <a href="{{ route('profile.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('profile.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                        <span>🔐</span>
                        <span>Super Admin Profile</span>
                    </a>

                @else
                    <!-- ============================================== -->
                    <!-- 2. TENANT BILLING WORKSPACE NAVIGATION         -->
                    <!-- ============================================== -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dashboard') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>

                    @if($user->hasPermission('invoices'))
                    <a href="{{ route('invoices.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('invoices.*') && !request()->has('tab') && !request()->has('type') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <span>📄</span>
                            <span>Invoices Master</span>
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono">
                            {{ \App\Models\Invoice::count() }}
                        </span>
                    </a>

                    <a href="{{ route('invoices.create') }}" 
                       class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 pl-9">
                        <span>+ Create Invoice</span>
                    </a>

                    <a href="{{ route('invoices.index', ['type' => 'proforma']) }}" 
                       class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 pl-9">
                        <span>📋 Proforma & Quotes</span>
                    </a>

                    <a href="{{ route('invoices.index', ['tab' => 'trash']) }}" 
                       class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 pl-9">
                        <span>🗑️ Trash Bin</span>
                        <span class="text-[10px] text-rose-400 font-mono">
                            {{ \App\Models\Invoice::onlyTrashed()->count() }}
                        </span>
                    </a>
                    @endif

                    @if($user->hasPermission('customers') || $user->hasPermission('products'))
                    <div class="pt-3 mt-3 border-t border-slate-800">
                        <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Business Master</span>
                    </div>

                    @if($user->hasPermission('customers'))
                    <a href="{{ route('customers.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('customers.*') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>👥</span>
                        <span>Customers & Parties</span>
                    </a>
                    @endif

                    @if($user->hasPermission('products'))
                    <a href="{{ route('products.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('products.*') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>📦</span>
                        <span>Products & Services</span>
                    </a>
                    @endif
                    @endif

                    <div class="pt-3 mt-3 border-t border-slate-800">
                        <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Compliance & Settings</span>
                    </div>

                    @if($user->hasPermission('reports'))
                    <a href="{{ route('reports.gstr1') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('reports.*') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <span>📈</span>
                            <span>GSTR-1 Reports</span>
                        </span>
                        <span class="text-[9px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded font-mono font-bold">CA READY</span>
                    </a>
                    @endif

                    @if($user->isCompanyAdmin())
                    <a href="{{ route('team.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('team.*') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <span>🛡️</span>
                            <span>Team & Staff Roles</span>
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono">
                            {{ \App\Models\User::where('company_id', $company->id)->where('id', '!=', $user->id)->count() }}
                        </span>
                    </a>
                    @endif

                    @if($user->hasPermission('settings'))
                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('settings.*') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>⚙️</span>
                        <span>Company Settings</span>
                    </a>
                    @endif

                    <a href="{{ route('profile.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('profile.*') ? $theme['sidebar_active'] : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>🔒</span>
                        <span>Profile & Security</span>
                    </a>
                @endif

            </nav>

            <!-- User Status Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center font-bold text-white text-xs">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="truncate max-w-[130px]">
                            <div class="text-xs font-bold text-white truncate">{{ $user->name }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $user->email }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-xl text-xs font-bold transition-colors">
                            Exit
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-100/70">
            
            <!-- Active Impersonation Top Banner -->
            @if(session('impersonator_id'))
            <div class="bg-gradient-to-r from-amber-600 via-rose-600 to-purple-600 text-white px-4 sm:px-8 py-2.5 text-xs font-bold flex items-center justify-between shadow-lg sticky top-0 z-40">
                <div class="flex items-center gap-2">
                    <span>👑 SUPER ADMIN IMPERSONATION ACTIVE:</span>
                    <span>Managing <strong>{{ $company->name }}</strong> as an administrator.</span>
                </div>
                <form action="{{ route('superadmin.stop_impersonate') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-white text-rose-700 rounded-lg text-xs font-black hover:bg-rose-50 transition-all shadow-sm">
                        Return to Super Admin &rarr;
                    </button>
                </form>
            </div>
            @endif

            <!-- Top Header Navbar -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h2 class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight">
                        {{ $header ?? 'GST Billing & ERP Portal' }}
                    </h2>
                </div>

                <div class="flex items-center gap-3">
                    @if($isSuperAdmin)
                        <!-- SUPER ADMIN CONTROLS (NO BROKEN TENANT INVOICE BUTTON) -->
                        <a href="{{ route('register') }}" target="_blank" 
                           class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition-all">
                            <span>+ Onboard Company</span>
                        </a>

                        <button type="button" @click="showImpersonateModal = true"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold shadow-sm transition-all">
                            <span>⚡</span> Impersonate Tenant
                        </button>
                    @else
                        <!-- TENANT BILLING CONTROLS -->
                        @if($company)
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 text-xs font-medium text-slate-700">
                            <span class="text-slate-400">Tax Mode:</span>
                            <span class="font-bold uppercase text-slate-900">
                                {{ $company->tax_mode === 'detailed' ? 'Split CGST/SGST' : 'Simple 18%' }}
                            </span>
                        </div>
                        @endif

                        <a href="{{ route('invoices.create') }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-xs font-bold shadow-md transition-all hover:scale-[1.01] active:scale-[0.99] {{ $theme['button_primary'] }}">
                            <span>+ New Invoice</span>
                        </a>
                    @endif
                </div>
            </header>

            <!-- Alerts / Notifications -->
            <div class="px-4 sm:px-8 pt-5">
                @if(session('success'))
                <div class="mb-4 flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-sm text-xs font-semibold" x-data="{ show: true }" x-show="show">
                    <span>✓ {{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-700 hover:text-emerald-900 font-bold">✕</button>
                </div>
                @endif

                @if(session('info'))
                <div class="mb-4 flex items-center justify-between p-3.5 rounded-2xl bg-slate-900 text-white shadow-sm text-xs font-medium" x-data="{ show: true }" x-show="show">
                    <span>ℹ️ {{ session('info') }}</span>
                    <button @click="show = false" class="text-white/60 hover:text-white font-bold">✕</button>
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-4 flex items-center justify-between p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 shadow-sm text-xs font-semibold" x-data="{ show: true }" x-show="show">
                    <span>⚠️ {{ session('warning') }}</span>
                    <button @click="show = false" class="text-amber-700 hover:text-amber-900 font-bold">✕</button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 shadow-sm text-xs">
                    <div class="font-bold mb-1">Please review the following errors:</div>
                    <ul class="list-disc pl-5 space-y-0.5 font-medium">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <!-- Page Main Content Slot -->
            <main class="flex-1 px-4 sm:px-8 pb-12 pt-1">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- QUICK IMPERSONATION MODAL (FOR SUPER ADMIN) -->
    @if($isSuperAdmin)
    <div x-show="showImpersonateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4 p-0">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="showImpersonateModal = false"></div>
            
            <div class="relative inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-slate-950 p-6 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-white flex items-center gap-2">
                            <span>⚡ Select Tenant to Manage</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Jump directly into any company's billing portal</p>
                    </div>
                    <button type="button" @click="showImpersonateModal = false" class="text-white/60 hover:text-white font-bold">✕</button>
                </div>

                <div class="p-6 space-y-3 max-h-96 overflow-y-auto">
                    @php $allTenants = \App\Models\Company::where('is_active', true)->where('approval_status', 'approved')->get(); @endphp
                    @forelse($allTenants as $t)
                    <div class="p-4 rounded-2xl border border-slate-200 hover:border-amber-400 bg-slate-50 hover:bg-amber-50/40 transition-all flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-xs text-slate-900">{{ $t->name }}</h4>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                GSTIN: {{ $t->gstin ?: 'Not set' }} · {{ $t->state }}
                            </div>
                        </div>
                        <form action="{{ route('superadmin.impersonate', $t->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white text-xs font-bold transition-all shadow-sm">
                                Enter Portal &rarr;
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="text-center py-6 text-slate-400 text-xs">No active companies found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    @stack('scripts')
</body>
</html>