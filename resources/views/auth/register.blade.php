<x-guest-layout title="Onboard Your Company | GST-SaaS">
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-950/40 border border-slate-200/80 overflow-hidden grid grid-cols-1 lg:grid-cols-12" 
         x-data="{ showPass: false, taxMode: 'simple' }">
        
        <!-- Left Brand Showcase Panel (5 Cols) - Matching Login Structure -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div>
                <!-- App Logo -->
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 font-bold text-xl">
                        G
                    </div>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight text-white flex items-center gap-1.5">
                            GST-SaaS
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono font-bold border border-emerald-500/30">ENTERPRISE</span>
                        </span>
                        <p class="text-xs text-slate-400">Multi-Company Billing Cloud</p>
                    </div>
                </div>

                <h2 class="text-2xl font-black tracking-tight leading-snug text-white mb-3">
                    Launch Your Dedicated GST Billing Workspace.
                </h2>
                <p class="text-xs text-slate-300 leading-relaxed mb-6">
                    Enterprise-grade multi-tenant architecture with cryptographic company data isolation, custom invoice numbering, and statutory CA-ready GSTR-1 compliance.
                </p>

                <!-- Value Props -->
                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Zero-Leakage Multi-Tenant Scoped Database</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Full 1-Page A4 Printing (Modern & Tally Formats)</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Simple 18% vs Detailed Split CGST/SGST Toggle</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Zero-Fee Dynamic UPI QR Codes & Dedicated SMTP</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-800">
                <p class="text-xs text-slate-400">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="font-bold text-emerald-400 hover:text-emerald-300 underline underline-offset-4 ml-1">
                        Sign in to Dashboard &rarr;
                    </a>
                </p>
            </div>
        </div>

        <!-- Right Form Panel (7 Cols) -->
        <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-center max-h-[90vh] overflow-y-auto">
            
            <!-- Auth Tab Switcher -->
            <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl mb-5 max-w-xs border border-slate-200">
                <a href="{{ route('login') }}" class="flex-1 py-1.5 text-xs font-semibold text-center text-slate-600 hover:text-slate-900 rounded-lg transition-colors">
                    Sign In
                </a>
                <button type="button" class="flex-1 py-1.5 text-xs font-bold rounded-lg bg-white text-slate-900 shadow-sm">
                    Register Tenant
                </button>
            </div>

            <div class="mb-5">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block mb-1">Company Onboarding</span>
                <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">Register New Company</h3>
                <p class="text-xs text-slate-500 mt-0.5">Set up your business profile and administrator credentials.</p>
            </div>

            @if($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                @foreach($errors->all() as $err)
                    <div>⚠️ {{ $err }}</div>
                @endforeach
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- 1. Company Profile -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Company / Business Name *</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Apex Technologies Pvt Ltd"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">State *</label>
                        <input type="text" name="state" value="{{ old('state', 'Bihar') }}" required placeholder="e.g. Bihar, Maharashtra"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">GSTIN (Optional)</label>
                        <input type="text" name="gstin" value="{{ old('gstin') }}" placeholder="10AAAAA0000A1Z5"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 uppercase font-mono text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- 2. Tax Mode Selector -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tax Mode Preference *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2.5 rounded-xl border-2 cursor-pointer transition-all text-left" :class="taxMode === 'simple' ? 'border-emerald-600 bg-emerald-50/40 text-emerald-900' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="tax_mode" value="simple" x-model="taxMode" class="hidden">
                            <div class="font-bold text-xs">Simple GST (18%)</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Single line total GST</div>
                        </label>
                        <label class="p-2.5 rounded-xl border-2 cursor-pointer transition-all text-left" :class="taxMode === 'split_cgst_sgst' ? 'border-emerald-600 bg-emerald-50/40 text-emerald-900' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="tax_mode" value="split_cgst_sgst" x-model="taxMode" class="hidden">
                            <div class="font-bold text-xs">Split CGST + SGST</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Separate central/state tax</div>
                        </label>
                    </div>
                </div>

                <!-- 3. Admin Account Credentials -->
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Company Owner Account</span>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Your Full Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Rahul Sharma"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Business Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="rahul@company.com"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Password *</label>
                                <input :type="showPass ? 'text' : 'password'" name="password" required placeholder="••••••••"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm Password *</label>
                                <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required placeholder="••••••••"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                                <input type="checkbox" @click="showPass = !showPass" class="w-3.5 h-3.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Show Passwords</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2 mt-2">
                    <span>Create Company Workspace</span>
                    <span>&rarr;</span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="text-xs text-slate-500">
                    By onboarding, your company data is isolated with unique cryptographic tenant scoping.
                </p>
            </div>

        </div>

    </div>
</x-guest-layout>