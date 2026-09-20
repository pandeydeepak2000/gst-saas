<x-guest-layout title="Sign In to GST-SaaS">
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-300/50 border border-slate-200/80 overflow-hidden grid grid-cols-1 lg:grid-cols-12" 
         x-data="{ showPass: false, email: '{{ old('email', 'admin@acme.com') }}', password: 'password', showTour: false }">
        
        <!-- Left Brand Showcase Panel (5 Cols) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div>
                <!-- App Logo -->
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 font-bold text-xl">
                        G
                    </div>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight text-white flex items-center gap-1.5">
                            GST-SaaS
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-brand-500/30 text-brand-300 font-mono font-bold border border-brand-500/40">ENTERPRISE</span>
                        </span>
                        <p class="text-xs text-slate-400">Multi-Company Billing Cloud</p>
                    </div>
                </div>

                <h2 class="text-2xl font-black tracking-tight leading-snug text-white mb-3">
                    Next-Gen GST Billing for Indian Enterprises.
                </h2>
                <p class="text-xs text-slate-300 leading-relaxed mb-6">
                    Multi-tenant data isolation, custom invoice formats, single-page print engine, and enterprise security.
                </p>

                <!-- Value Props -->
                <div class="space-y-2.5 text-xs text-slate-300">
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
                        <span>Simple 18% Single Line vs Split CGST/SGST</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Anti-Takeover Protected Registered Emails</span>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="button" @click="showTour = true" 
                            class="w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-bold border border-white/20 transition-all flex items-center justify-center gap-2">
                        <span>✨</span> Compare vs Zoho Books & Tour
                    </button>
                </div>
            </div>

            <!-- Quick Demo Accounts Switcher -->
            <div class="mt-8 pt-6 border-t border-slate-800">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">⚡ 1-Click Demo Fill:</span>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="email = 'admin@acme.com'; password = 'password'" 
                            class="text-left p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-xs transition-colors">
                        <div class="font-bold text-white text-[11px] truncate">Acme Info</div>
                        <div class="text-[9px] text-brand-300 truncate">Split CGST/SGST</div>
                    </button>
                    <button type="button" @click="email = 'admin@bharat.com'; password = 'password'" 
                            class="text-left p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-xs transition-colors">
                        <div class="font-bold text-white text-[11px] truncate">Bharat Trade</div>
                        <div class="text-[9px] text-emerald-300 truncate">Simple 18% GST</div>
                    </button>
                    <button type="button" @click="email = 'superadmin@gstsaas.com'; password = 'password'" 
                            class="text-left p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-indigo-700 text-xs transition-colors">
                        <div class="font-bold text-rose-300 text-[11px] truncate">👑 Super Admin</div>
                        <div class="text-[9px] text-indigo-300 truncate">Platform Control</div>
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

            @if(session('info'))
            <div class="mb-4 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-medium">
                {{ session('info') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                @foreach($errors->all() as $err)
                    <div>⚠️ {{ $err }}</div>
                @endforeach
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
                            📧
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
                            🔑
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
                    <span>&rarr;</span>
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

        <!-- MODAL: PLATFORM TOUR & ZOHO COMPARISON -->
        <div x-show="showTour" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showTour" x-transition.opacity class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="showTour = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showTour" x-transition.scale class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200">
                    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-6 text-white flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-black text-white">GST-SaaS vs Zoho Books & Vyapar</h3>
                            <p class="text-xs text-indigo-200 mt-0.5">Enterprise Features Built Specifically for Indian Businesses</p>
                        </div>
                        <button type="button" @click="showTour = false" class="text-white/60 hover:text-white text-xl font-bold">✕</button>
                    </div>

                    <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto text-xs">
                        <table class="w-full text-left border-collapse border border-slate-200 rounded-xl overflow-hidden">
                            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px]">
                                <tr>
                                    <th class="p-3 border-b border-slate-200">Feature</th>
                                    <th class="p-3 border-b border-slate-200 text-brand-700 bg-brand-50/50">GST-SaaS</th>
                                    <th class="p-3 border-b border-slate-200 text-slate-500">Zoho Books</th>
                                    <th class="p-3 border-b border-slate-200 text-slate-500">Vyapar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="p-3 font-semibold text-slate-900">Custom Invoice Numbering</td>
                                    <td class="p-3 text-emerald-600 font-bold bg-brand-50/30">✓ Fully Editable Any Time</td>
                                    <td class="p-3 text-slate-500">Fixed Pattern Only</td>
                                    <td class="p-3 text-slate-500">Sequential Only</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-semibold text-slate-900">Tax Display Mode</td>
                                    <td class="p-3 text-emerald-600 font-bold bg-brand-50/30">✓ Simple 18% OR Split CGST/SGST</td>
                                    <td class="p-3 text-slate-500">Rigid Split Only</td>
                                    <td class="p-3 text-slate-500">Basic</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-semibold text-slate-900">A4 Single-Page Print Engine</td>
                                    <td class="p-3 text-emerald-600 font-bold bg-brand-50/30">✓ Zero Page Overflow Void</td>
                                    <td class="p-3 text-slate-500">Often Spills to 2 Pages</td>
                                    <td class="p-3 text-slate-500">Basic Template</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-semibold text-slate-900">Multi-Tenant Isolation</td>
                                    <td class="p-3 text-emerald-600 font-bold bg-brand-50/30">✓ Scoped Database Isolation</td>
                                    <td class="p-3 text-slate-500">Multi-Org with high cost</td>
                                    <td class="p-3 text-slate-500">Offline Single Device</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-semibold text-slate-900">Security: Anti-Takeover Email</td>
                                    <td class="p-3 text-emerald-600 font-bold bg-brand-50/30">✓ Super Admin Verification Gate</td>
                                    <td class="p-3 text-slate-500">OTP / Self-Serve</td>
                                    <td class="p-3 text-slate-500">No Guard</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-semibold text-slate-900">Dedicated SMTP & WhatsApp API</td>
                                    <td class="p-3 text-emerald-600 font-bold bg-brand-50/30">✓ Per-Company Dedicated Config</td>
                                    <td class="p-3 text-slate-500">Add-on Fee</td>
                                    <td class="p-3 text-slate-500">Desktop Only</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="flex justify-end pt-3">
                            <button type="button" @click="showTour = false" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs">
                                Close Tour
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-guest-layout>
