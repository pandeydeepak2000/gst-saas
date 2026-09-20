<x-guest-layout title="Set New Password">
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-300/50 border border-slate-200/80 overflow-hidden max-w-md mx-auto p-8 sm:p-10" x-data="{ showPass: false }">
        
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600 shadow-sm mx-auto mb-3 text-xl">
                🛡️
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Set New Password</h2>
            <p class="text-xs text-slate-500 mt-1">Please enter your new secure password below.</p>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address *</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required readonly
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 text-sm cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">New Password (Min 8 Chars) *</label>
                <div class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password" required placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                        <span x-show="!showPass">👁️</span>
                        <span x-show="showPass" x-cloak>🙈</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm New Password *</label>
                <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required placeholder="••••••••"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-600/20 transition-all">
                Reset Password & Sign In
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-600 hover:text-brand-600">
                Cancel and return to Sign In
            </a>
        </div>
    </div>
</x-guest-layout>
