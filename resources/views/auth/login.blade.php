<x-guest-layout title="Sign In to GST-SaaS">
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-300/50 border border-slate-200/80 overflow-hidden grid grid-cols-1 lg:grid-cols-12" x-data="{ showPass: false, email: '{{ old('email', 'admin@acme.com') }}', password: 'password' }">
        
        <!-- Left Brand Showcase Panel (5 Cols) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div>
                <!-- App Logo -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 font-bold text-xl">
                        G
                    </div>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight text-white flex items-center gap-1.5">
                            GST-SaaS
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-brand-500/30 text-brand-300 font-mono font-bold border border-brand-500/40">ERP</span>
                        </span>
                        <p class="text-xs text-slate-400">Multi-Company Billing Cloud</p>
                    </div>
                </div>

                <h2 class="text-2xl font-black tracking-tight leading-snug text-white mb-3">
                    Next-Gen Billing for Growing Businesses.
                </h2>
                <p class="text-xs text-slate-300 leading-relaxed mb-6">
                    Multi-tenant data isolation, custom invoice numbers, and flexible Simple 18% vs Detailed Split GST modes.
                </p>

                <!-- Value Props -->
                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Strict Tenant Data Privacy & Zero Leakage</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Freely Editable Custom Invoice Numbering</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Simple 18% Single Line vs Split CGST/SGST Toggle</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Soft-Delete Trash Bin with 1-Click Recovery</span>
                    </div>
                </div>
            </div>

            <!-- Quick Demo Accounts Switcher -->
            <div class="mt-8 pt-6 border-t border-slate-800">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">⚡ 1-Click Demo Fill:</span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="email = 'admin@acme.com'; password = 'password'" 
                            class="text-left p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-xs transition-colors">
                        <div class="font-bold text-white text-[11px] truncate">Acme Infotech</div>
                        <div class="text-[10px] text-brand-300 truncate">Split CGST/SGST</div>
                    </button>
                    <button type="button" @click="email = 'admin@bharat.com'; password = 'password'" 
                            class="text-left p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-xs transition-colors">
                        <div class="font-bold text-white text-[11px] truncate">Bharat Trade</div>
                        <div class="text-[10px] text-emerald-300 truncate">Simple 18% GST</div>
                    </button>
                </div>
            </div>
        </div>
        <!-- Right Form Panel (7 Cols) -->
        <div class="lg:col-span-7 p-8 sm:p-12 flex flex-col justify-center">
            
            <div class="mb-6">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 block mb-1">Company Access</span>
                <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sign in to your account</h3>
                <p class="text-xs text-slate-500 mt-1">Enter your business credentials to access your billing workspace.</p>
            </div>

            @if(session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Business Email -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Business Email *</label>
                    <div class="relative">
                        <input type="email" name="email" x-model="email" required autofocus placeholder="owner@company.com"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Password with Show/Hide Eye Toggle -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Password *</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 underline underline-offset-2">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="password" required placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                            <span x-show="!showPass" title="Show password">👁️</span>
                            <span x-show="showPass" title="Hide password" x-cloak>🙈</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs py-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span>Remember this device for 30 days</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg shadow-brand-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                    <span>Sign In to Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Want to register a new company? 
                    <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-800 underline underline-offset-4 ml-1">
                        Register New Tenant &rarr;
                    </a>
                </p>
            </div>

        </div>
    </div>
</x-guest-layout>
