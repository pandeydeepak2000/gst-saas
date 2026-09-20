<x-guest-layout title="Onboard Your Company">
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-300/50 border border-slate-200/80 overflow-hidden max-w-2xl mx-auto p-8 sm:p-10" x-data="{ showPass: false, taxMode: 'simple' }">
        
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 mx-auto mb-3 font-bold text-xl">
                G
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Onboard Your Company</h2>
            <p class="text-xs text-slate-500 mt-1">Set up your dedicated multi-tenant GST billing environment in seconds.</p>
        </div>

        @if($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="font-bold mb-1">Please fix the following:</div>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Section 1: Business Identity -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600 block">1. Company Profile</span>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Company / Business Name *</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Kumar Electronics or Apex Infotech"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Operating State *</label>
                        <input type="text" name="state" value="{{ old('state', 'Bihar') }}" required
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">GSTIN (Optional)</label>
                        <input type="text" name="gstin" value="{{ old('gstin') }}" placeholder="10AAAAA0000A1Z5"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 uppercase font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    </div>
                </div>
            </div>
            <!-- Section 2: Tax Mode Preference -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600 block">2. Tax Mode Preference</span>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="p-3 rounded-xl border-2 cursor-pointer transition-all bg-white" :class="taxMode === 'simple' ? 'border-brand-600 ring-2 ring-brand-500/20' : 'border-slate-200 hover:border-slate-300'">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="tax_mode" value="simple" x-model="taxMode" class="text-brand-600 focus:ring-brand-500">
                            <span class="font-bold text-xs text-slate-900">Simple GST (18%)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 ml-5">Single-line total GST display. Clean and compact for retail.</p>
                    </label>

                    <label class="p-3 rounded-xl border-2 cursor-pointer transition-all bg-white" :class="taxMode === 'detailed' ? 'border-brand-600 ring-2 ring-brand-500/20' : 'border-slate-200 hover:border-slate-300'">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="tax_mode" value="detailed" x-model="taxMode" class="text-brand-600 focus:ring-brand-500">
                            <span class="font-bold text-xs text-slate-900">Split CGST/SGST</span>
                        </div>
                        <p class="text-[11px] text-slate-500 ml-5">Detailed CGST+SGST/IGST breakdown with HSN table for B2B.</p>
                    </label>
                </div>
            </div>

            <!-- Section 3: Owner Account Credentials -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600 block">3. Company Administrator</span>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Owner / Admin Name *</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="e.g. Ramesh Kumar"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobile / Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91..."
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Business Email (Login ID) *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@mycompany.com"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" required placeholder="Min 8 characters"
                                   class="w-full px-3.5 py-2 pr-10 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                                <span x-show="!showPass">👁️</span>
                                <span x-show="showPass" x-cloak>🙈</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password *</label>
                        <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required placeholder="Repeat password"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg shadow-brand-600/25 active:scale-[0.99] transition-all">
                Complete Setup & Launch Dashboard
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500">
            Already registered? 
            <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-800 underline underline-offset-4 ml-1">
                Sign In to Existing Account &rarr;
            </a>
        </div>
    </div>
</x-guest-layout>
