<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} | GST-SaaS Cloud ERP</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800" x-data="{ mobileSidebarOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">
        
        <div x-show="mobileSidebarOpen" x-cloak 
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
             @click="mobileSidebarOpen = false">
        </div>

        <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 border-r border-slate-800 shadow-xl">
            
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950/40">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 font-bold text-lg">
                        G
                    </div>
                    <div>
                        <span class="font-bold text-lg text-white tracking-tight flex items-center gap-1.5">
                            GST-SaaS
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-300 font-mono font-medium border border-brand-500/30">PRO</span>
                        </span>
                        <p class="text-xs text-slate-400 truncate max-w-[140px]">{{ auth()->user()->company->name ?? 'Multi-Tenant' }}</p>
                    </div>
                </a>
                <button @click="mobileSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">&times;</button>
            </div>

            @if(auth()->user()->company)
            <div class="p-4 mx-4 mt-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Tenant</span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full {{ auth()->user()->company->tax_mode === 'detailed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ auth()->user()->company->tax_mode === 'detailed' ? 'bg-emerald-400' : 'bg-blue-400' }}"></span>
                        {{ auth()->user()->company->tax_mode === 'detailed' ? 'Split GST' : 'Simple GST' }}
                    </span>
                </div>
                <div class="font-semibold text-sm text-white truncate">{{ auth()->user()->company->name }}</div>
                <div class="text-xs font-mono text-slate-400 mt-0.5 truncate">
                    GSTIN: {{ auth()->user()->company->gstin ?: 'Not Configured' }}
                </div>
            </div>
            @endif

            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    Dashboard
                </a>

                <a href="{{ route('invoices.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('invoices.*') && !request()->has('tab') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span>Invoices</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono">
                        {{ \App\Models\Invoice::count() }}
                    </span>
                </a>

                <a href="{{ route('invoices.create') }}" 
                   class="flex items-center gap-3 px-3.5 py-2 rounded-lg text-sm font-medium text-brand-300 hover:bg-brand-500/10 pl-8">
                    + Create Invoice
                </a>

                <a href="{{ route('invoices.index', ['tab' => 'trash']) }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->get('tab') === 'trash' ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span>Trash Bin</span>
                    @php $trashCount = \App\Models\Invoice::onlyTrashed()->count(); @endphp
                    @if($trashCount > 0)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 font-mono font-bold">
                            {{ $trashCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('customers.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('customers.*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    Customers
                </a>

                <a href="{{ route('products.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    Products & Services
                </a>

                <div class="pt-4 mt-4 border-t border-slate-800/60">
                    <span class="px-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Settings</span>
                </div>

                <a href="{{ route('settings.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span>Company Settings</span>
                    <span class="text-[10px] text-amber-300 font-mono">Tax & Prefix</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center font-bold text-white text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="truncate">
                            <div class="text-sm font-semibold text-white truncate max-w-[120px]">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-slate-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg text-xs font-semibold">
                            Exit
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        <!-- Main Content Column -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-8 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight">{{ $header ?? 'Dashboard' }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    @if(auth()->user()->company)
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-medium text-slate-700">
                        <span class="text-slate-400">Tax Mode:</span>
                        <span class="font-bold text-brand-700 uppercase">
                            {{ auth()->user()->company->tax_mode === 'detailed' ? 'Detailed Split' : 'Simple GST (18%)' }}
                        </span>
                    </div>
                    @endif

                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition-all hover:shadow-md">
                        <span>+ New Invoice</span>
                    </a>
                </div>
            </header>

            <div class="px-4 sm:px-8 pt-6">
                @if(session('success'))
                <div class="mb-4 flex items-center justify-between p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm" x-data="{ show: true }" x-show="show">
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-900">&times;</button>
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-4 flex items-center justify-between p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 shadow-sm" x-data="{ show: true }" x-show="show">
                    <span class="font-medium text-sm">{{ session('warning') }}</span>
                    <button @click="show = false" class="text-amber-600 hover:text-amber-900">&times;</button>
                </div>
                @endif

                @if(session('danger'))
                <div class="mb-4 flex items-center justify-between p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm" x-data="{ show: true }" x-show="show">
                    <span class="font-medium text-sm">{{ session('danger') }}</span>
                    <button @click="show = false" class="text-rose-600 hover:text-rose-900">&times;</button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                    <div class="font-bold text-sm mb-1">Please check errors:</div>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <main class="flex-1 px-4 sm:px-8 pb-12">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
