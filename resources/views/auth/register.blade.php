<x-guest-layout title="Register New Company">
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-white tracking-tight">Onboard Your Company</h2>
            <p class="text-sm text-slate-400 mt-1">Multi-tenant Cloud GST Invoicing</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Company / Business Name *</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Kumar Electronics"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Owner Name *</label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="Your name"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91..."
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Business Email (Login) *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="owner@business.com"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">State *</label>
                    <input type="text" name="state" value="{{ old('state', 'Bihar') }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">GSTIN (Optional)</label>
                    <input type="text" name="gstin" value="{{ old('gstin') }}" placeholder="10AAAAA0000A1Z5"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white uppercase placeholder-slate-500 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tax Display Mode Preference</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-900/80 border border-slate-700 cursor-pointer hover:border-brand-500">
                        <input type="radio" name="tax_mode" value="simple" checked class="text-brand-600 focus:ring-brand-500">
                        <div>
                            <div class="text-xs font-bold text-white">Simple GST</div>
                            <div class="text-[10px] text-slate-400">Single line (GST 18%)</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-900/80 border border-slate-700 cursor-pointer hover:border-brand-500">
                        <input type="radio" name="tax_mode" value="detailed" class="text-brand-600 focus:ring-brand-500">
                        <div>
                            <div class="text-xs font-bold text-white">Split GST</div>
                            <div class="text-[10px] text-slate-400">CGST + SGST / IGST</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold shadow-lg shadow-brand-600/30 transition-all">
                Create Account & Company
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white">Already have an account? Sign In</a>
        </div>
    </div>
</x-guest-layout>
