<x-guest-layout title="403 Forbidden">
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-950/20 border border-slate-200/80 p-8 sm:p-12 text-center max-w-lg mx-auto">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-3xl font-black mx-auto mb-4 border border-amber-500/20">
            🛡️
        </div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Access Restricted (403)</h2>
        <p class="text-xs text-slate-500 leading-relaxed mb-6">
            {{ $exception->getMessage() ?: 'You do not have administrative clearance to access this module or company workspace.' }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                Return to Dashboard &rarr;
            </a>
            <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-all">
                    Sign Out & Switch User
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>