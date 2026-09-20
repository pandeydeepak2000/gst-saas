<x-app-layout header="Invoices & Billing Register">
    <div class="space-y-6">
        
        <!-- Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Invoices Directory</h2>
                <p class="text-sm text-slate-500">Search, filter, edit, trash and print customer GST tax invoices.</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition-all">
                + Create Invoice
            </a>
        </div>

        <!-- Filter Status Tabs & Live Search Bar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('invoices.index') }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $tab === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Invoices
                </a>
                <a href="{{ route('invoices.index', ['tab' => 'paid']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $tab === 'paid' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Paid
                </a>
                <a href="{{ route('invoices.index', ['tab' => 'unpaid']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $tab === 'unpaid' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Unpaid
                </a>
                <a href="{{ route('invoices.index', ['tab' => 'partially_paid']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $tab === 'partially_paid' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' }}">
                    Partial
                </a>
                <a href="{{ route('invoices.index', ['tab' => 'trash']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5 {{ $tab === 'trash' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    <span>Trash Bin</span>
                    @if($trashCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'trash' ? 'bg-white text-rose-700 font-bold' : 'bg-rose-200 text-rose-800' }}">{{ $trashCount }}</span>
                    @endif
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('invoices.index') }}" method="GET" class="flex items-center gap-2">
                @if($tab !== 'all')
                    <input type="hidden" name="tab" value="{{ $tab }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Invoice # or Customer..."
                       class="px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs w-64 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <button type="submit" class="px-3.5 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('invoices.index', ['tab' => $tab]) }}" class="text-xs text-slate-500 hover:text-slate-800">Reset</a>
                @endif
            </form>
        </div>

        @if($tab === 'trash')
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-bold">Trash Recovery Active:</span> Invoices in Trash can be restored anytime with 1-click. Permanent delete will permanently purge the invoice.
            </div>
            <a href="{{ route('invoices.index') }}" class="font-bold text-amber-800 underline">Return to Active Invoices</a>
        </div>
        @endif
        <!-- Invoices Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Date / Due</th>
                            <th class="py-3 px-4">Tax Mode</th>
                            <th class="py-3 px-4 text-right">Taxable</th>
                            <th class="py-3 px-4 text-right">Grand Total</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $inv->trashed() ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-700">
                                @if(!$inv->trashed())
                                <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                                @else
                                <span class="line-through text-slate-400">{{ $inv->invoice_number }}</span>
                                <span class="text-[10px] text-rose-500 font-sans ml-1">(Trashed)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $inv->customer->name ?? 'Walk-in' }}</div>
                                @if($inv->customer && $inv->customer->company_name)
                                    <div class="text-xs text-slate-400">{{ $inv->customer->company_name }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <div>{{ $inv->invoice_date->format('d M, Y') }}</div>
                                @if($inv->due_date)
                                    <div class="text-[11px] text-slate-400">Due: {{ $inv->due_date->format('d M, Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded {{ $inv->tax_mode === 'detailed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $inv->tax_mode === 'detailed' ? 'Split Tax' : 'Simple 18%' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-xs text-slate-600">
                                ₹{{ number_format($inv->taxable_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                ₹{{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($inv->status === 'unpaid' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600') }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if(!$inv->trashed())
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="px-2 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700">
                                        Print
                                    </a>
                                    <a href="{{ route('invoices.edit', $inv->id) }}" class="px-2 py-1 text-xs font-semibold rounded bg-brand-50 hover:bg-brand-100 text-brand-700">
                                        Edit
                                    </a>
                                    <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('Move Invoice #{{ $inv->invoice_number }} to Trash?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 text-xs font-semibold rounded bg-rose-50 hover:bg-rose-100 text-rose-700" title="Move to Trash">
                                            Trash
                                        </button>
                                    </form>
                                </div>
                                @else
                                <div class="inline-flex items-center gap-1.5">
                                    <form action="{{ route('invoices.restore', $inv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded bg-emerald-600 hover:bg-emerald-700 text-white">
                                            Restore
                                        </button>
                                    </form>
                                    <form action="{{ route('invoices.force_delete', $inv->id) }}" method="POST" onsubmit="return confirm('PERMANENTLY DELETE invoice #{{ $inv->invoice_number }}? This cannot be undone.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 text-xs font-semibold rounded bg-slate-200 hover:bg-rose-600 hover:text-white text-slate-700">
                                            Delete Forever
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                @if($tab === 'trash')
                                    Trash bin is empty. No deleted invoices.
                                @else
                                    No invoices matching your filter criteria.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
