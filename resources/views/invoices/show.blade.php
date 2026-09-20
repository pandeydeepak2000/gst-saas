<x-app-layout header="Invoice #{{ $invoice->invoice_number }}">
    <div class="max-w-5xl mx-auto space-y-6">
        
        <!-- Action Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('invoices.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600">
                    &larr; Back
                </a>
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 font-mono flex items-center gap-2">
                        <span>{{ $invoice->invoice_number }}</span>
                        @if($invoice->trashed())
                            <span class="text-xs px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 font-sans">Trashed</span>
                        @endif
                    </h2>
                    <p class="text-xs text-slate-500">Issued on {{ $invoice->invoice_date->format('d F, Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(!$invoice->trashed())
                    <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print A4 Invoice
                    </a>
                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="px-4 py-2 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold border border-brand-200">
                        Edit Invoice
                    </a>
                    <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Move this invoice to Trash?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold border border-rose-200">
                            Trash
                        </button>
                    </form>
                @else
                    <form action="{{ route('invoices.restore', $invoice->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                            Restore from Trash
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Invoice Preview Paper -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-md p-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row justify-between gap-6 pb-6 border-b border-slate-200">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-600 block mb-1">Tax Invoice</span>
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ auth()->user()->company->name }}</h1>
                    <p class="text-xs text-slate-500 max-w-sm mt-1">{{ auth()->user()->company->address ?: 'Business Address' }}, {{ auth()->user()->company->city }}, {{ auth()->user()->company->state }}</p>
                    <div class="text-xs font-mono text-slate-700 mt-2 space-y-0.5">
                        @if(auth()->user()->company->gstin)<div>GSTIN: <strong>{{ auth()->user()->company->gstin }}</strong></div>@endif
                        @if(auth()->user()->company->phone)<div>Phone: {{ auth()->user()->company->phone }}</div>@endif
                    </div>
                </div>

                <div class="sm:text-right">
                    <div class="font-mono text-xl font-black text-brand-700">{{ $invoice->invoice_number }}</div>
                    <div class="text-xs text-slate-500 mt-1">Date: <strong>{{ $invoice->invoice_date->format('d M, Y') }}</strong></div>
                    @if($invoice->due_date)
                        <div class="text-xs text-slate-500">Due: <strong>{{ $invoice->due_date->format('d M, Y') }}</strong></div>
                    @endif
                    <div class="mt-2">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $invoice->status }}
                        </span>
                    </div>
                    <div class="mt-2 text-[11px] font-semibold text-slate-500">
                        Tax Format: <span class="text-brand-700 uppercase">{{ $invoice->tax_mode === 'detailed' ? 'Split CGST/SGST' : 'Simple GST (18%)' }}</span>
                    </div>
                </div>
            </div>

            <!-- Bill To Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Bill To / Customer:</span>
                    <div class="font-bold text-slate-900 text-base">{{ $invoice->customer->name ?? 'Direct Customer' }}</div>
                    @if($invoice->customer && $invoice->customer->company_name)
                        <div class="text-xs text-slate-600">{{ $invoice->customer->company_name }}</div>
                    @endif
                    <div class="text-xs text-slate-500 mt-1">{{ $invoice->customer->billing_address ?? '' }}</div>
                    @if($invoice->customer && $invoice->customer->gstin)
                        <div class="text-xs font-mono text-slate-700 mt-1">GSTIN: <strong>{{ $invoice->customer->gstin }}</strong></div>
                    @endif
                </div>

                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Place of Supply:</span>
                    <div class="font-semibold text-slate-800 text-sm">{{ $invoice->sale_type === 'LOCAL' ? 'Intra-State: ' . auth()->user()->company->state : 'Inter-State (IGST)' }}</div>
                    @if($invoice->payment_method)
                        <div class="text-xs text-slate-500 mt-2">Payment Mode: <strong>{{ $invoice->payment_method }}</strong></div>
                    @endif
                </div>
            </div>
            <!-- Line Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700 text-xs uppercase font-bold border-y border-slate-200">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">#</th>
                            <th class="py-3 px-3">Description</th>
                            <th class="py-3 px-3 w-24">HSN</th>
                            <th class="py-3 px-3 w-20 text-center">Qty</th>
                            <th class="py-3 px-3 w-28 text-right">Rate</th>
                            <th class="py-3 px-3 w-20 text-center">GST</th>
                            <th class="py-3 px-3 w-32 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @foreach($invoice->items as $idx => $item)
                        <tr>
                            <td class="py-3 px-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3 font-sans font-medium text-slate-900">{{ $item->description }}</td>
                            <td class="py-3 px-3 text-slate-500">{{ $item->hsn_sac ?: '-' }}</td>
                            <td class="py-3 px-3 text-center">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="py-3 px-3 text-right">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="py-3 px-3 text-center font-bold text-slate-700">{{ $item->gst_percent }}%</td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900">₹{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary -->
            <div class="flex flex-col sm:flex-row justify-between gap-6 pt-4 border-t border-slate-200">
                <div class="text-xs text-slate-500 max-w-sm">
                    @if($invoice->notes)
                        <strong class="text-slate-700 block mb-1">Notes:</strong>
                        <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
                    @endif
                </div>

                <div class="w-full sm:w-80 space-y-2 font-mono text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Taxable Amount:</span>
                        <span class="font-bold">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                    </div>

                    @if($invoice->tax_mode === 'simple')
                        <div class="flex justify-between text-brand-700 font-bold border-t border-slate-100 pt-1">
                            <span>GST (Total Tax):</span>
                            <span>₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</span>
                        </div>
                    @else
                        @if($invoice->sale_type === 'LOCAL')
                            <div class="flex justify-between text-emerald-700">
                                <span>CGST:</span>
                                <span>₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-emerald-700">
                                <span>SGST:</span>
                                <span>₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                            </div>
                        @else
                            <div class="flex justify-between text-purple-700">
                                <span>IGST:</span>
                                <span>₹{{ number_format($invoice->igst_amount, 2) }}</span>
                            </div>
                        @endif
                    @endif

                    <div class="flex justify-between text-base font-extrabold text-slate-900 border-t-2 border-slate-900 pt-2">
                        <span>Grand Total:</span>
                        <span class="text-brand-700">₹{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
