<x-guest-layout title="Two-Factor Authentication (2FA) - GST-SaaS">
    <div class="max-w-md mx-auto bg-white rounded-3xl shadow-2xl shadow-slate-950/40 border border-slate-200/80 p-8 sm:p-10 space-y-6">
        
        <!-- Header & Security Badge -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-2xl shadow-lg shadow-emerald-500/20">
                🛡️
            </div>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">Two-Factor Authentication</h2>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                A 6-digit security verification code was generated for your account. Please enter it below to proceed.
            </p>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                <span>📧</span> {{ $user->email }}
            </div>
        </div>

        @if(session('info'))
            <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                ℹ️ {{ session('info') }}
            </div>
        @endif

        @if(session('success'))
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                @foreach($errors->all() as $err)
                    <div>⚠️ {{ $err }}</div>
                @endforeach
            </div>
        @endif

        @if(!empty($otpPreview))
            <!-- Local Development Testing Helper -->
            <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between">
                <div>
                    <span class="font-bold">⚡ Local Dev OTP:</span>
                    <code class="font-mono text-base font-black tracking-widest text-amber-900 ml-1">{{ $otpPreview }}</code>
                </div>
                <span class="text-[10px] text-amber-700 font-semibold uppercase">Autofill / Copy</span>
            </div>
        @endif

        <!-- 2FA Code Input Form -->
        <form action="{{ route('login.2fa.verify') }}" method="POST" class="space-y-4" x-data="{ code: '{{ $otpPreview ?? '' }}' }">
            @csrf

            <div>
                <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 text-center">
                    Enter 6-Digit Code
                </label>
                <div class="relative">
                    <input type="text" id="code" name="code" x-model="code" required maxlength="6" autofocus pattern="[0-9]{6}"
                           inputmode="numeric" placeholder="123456" autocomplete="one-time-code"
                           class="w-full text-center tracking-[0.6em] text-2xl font-mono font-black py-3 px-4 rounded-2xl border-2 border-slate-300 text-slate-900 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 focus:outline-none transition-all">
                </div>
                <p class="text-[11px] text-slate-400 text-center mt-1.5">Code expires in 10 minutes</p>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-xl shadow-slate-950/20 transition-all flex items-center justify-center gap-2">
                <span>Verify & Sign In</span>
                <span>&rarr;</span>
            </button>
        </form>

        <!-- Auxiliary Actions: Resend & Cancel -->
        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <form action="{{ route('login.2fa.resend') }}" method="POST">
                @csrf
                <button type="submit" class="font-bold text-emerald-700 hover:text-emerald-800 hover:underline flex items-center gap-1">
                    <span>🔄</span> Resend Code
                </button>
            </form>

            <a href="{{ route('login.2fa.cancel') }}" class="text-slate-500 hover:text-slate-800 hover:underline">
                Cancel & Return to Login
            </a>
        </div>

    </div>
</x-guest-layout>
