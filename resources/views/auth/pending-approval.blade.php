<x-guest-layout title="Onboarding Under Review">
    <div class="max-w-md mx-auto bg-white p-8 rounded-3xl border border-slate-200/80 shadow-2xl text-center space-y-5">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-2xl border border-amber-200/60 shadow-sm animate-pulse">
            ⏳
        </div>

        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Onboarding Under Review</h2>
            <p class="text-xs text-slate-500 mt-1">Your company account has been submitted and is currently pending verification by Platform Administration.</p>
        </div>

        @php
            $u = auth()->user();
            $comp = $u?->company;
        @endphp

        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-left text-xs space-y-1.5 font-mono">
            <div>Company: <strong class="text-slate-900">{{ $comp->name ?? 'N/A' }}</strong></div>
            <div>Admin: <strong class="text-slate-900">{{ $u->email ?? 'N/A' }}</strong></div>
            @if($comp?->gstin)
            <div>GSTIN: <strong class="text-brand-700">{{ $comp->gstin }}</strong></div>
            @endif
            <div>Status: <span class="text-amber-700 font-bold uppercase">Pending Verification</span></div>
        </div>

        <p class="text-[11px] text-slate-400">
            To protect all platform tenants from fraud and invalid GSTIN claims, our administrative team reviews each new registration before full billing access is unlocked.
        </p>

        <div class="pt-2 border-t border-slate-100 flex justify-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
