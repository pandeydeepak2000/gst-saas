<x-guest-layout title="Reset Password">
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-300/50 border border-slate-200/80 overflow-hidden max-w-md mx-auto p-8 sm:p-10">
        
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 shadow-sm mx-auto mb-3 text-xl">
                🔑
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Forgot Password?</h2>
            <p class="text-xs text-slate-500 mt-1">Enter your registered business email and we will generate a secure password reset link.</p>
        </div>

        @if(session('status'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
            <div class="font-bold">{{ session('status') }}</div>
            @if(session('reset_url'))
            <div class="mt-2 pt-2 border-t border-emerald-200">
                <span class="text-[11px] text-emerald-700 block mb-1">Instant Demo Reset Link:</span>
                <a href="{{ session('reset_url') }}" class="inline-block px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow-sm">
                    Click Here to Set New Password &rarr;
                </a>
            </div>
            @endif
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Business Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@acme.com"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-600/20 transition-all">
                Send Password Reset Link
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-600 hover:text-brand-600 flex items-center justify-center gap-1">
                <span>&larr;</span> Back to Sign In
            </a>
        </div>
    </div>
</x-guest-layout>
