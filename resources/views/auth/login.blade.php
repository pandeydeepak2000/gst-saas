<x-guest-layout title="Sign In">
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-600 to-brand-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">GST-SaaS Platform</h2>
            <p class="text-sm text-slate-400 mt-1">Multi-Company Cloud Billing & ERP</p>
        </div>

        @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Business Email</label>
                <input type="email" name="email" value="{{ old('email', 'admin@acme.com') }}" required autofocus
                       class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Password</label>
                <input type="password" name="password" value="password" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>

            <div class="flex items-center justify-between text-sm py-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-brand-600 focus:ring-brand-500">
                    <span class="text-slate-400 text-xs">Remember Me</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold shadow-lg shadow-brand-600/30 transition-all active:scale-[0.99]">
                Sign In to Portal
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-700/60 text-center">
            <p class="text-xs text-slate-400">
                Want to register a new company? 
                <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-semibold underline underline-offset-4">Sign Up Company</a>
            </p>
        </div>

        <div class="mt-6 p-3.5 rounded-xl bg-slate-900/60 border border-slate-700/50 text-xs text-slate-400 space-y-1">
            <div class="font-bold text-slate-300 uppercase tracking-wider text-[10px]">Demo Tenants (Password: password):</div>
            <div>🏢 <span class="text-white font-medium">Acme Infotech</span> (Detailed Split): <code class="text-brand-300">admin@acme.com</code></div>
            <div>🏬 <span class="text-white font-medium">Bharat Trade</span> (Simple GST 18%): <code class="text-brand-300">admin@bharat.com</code></div>
        </div>
    </div>
</x-guest-layout>
