@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ recordModalOpen: false, copyNotice: false }">

    <!-- HEADER / ACTIONS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 transition-colors shadow-sm">
                ←
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider {{ $invoice->type === 'proforma' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-brand-100 text-brand-800 border border-brand-200' }}">
                        {{ $invoice->type === 'proforma' ? 'Proforma / Quotation' : 'Official Tax Invoice' }}
                    </span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider
                        {{ $invoice->status === 'paid' || $invoice->balance_amount <= 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($invoice->status === 'partially_paid' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-rose-100 text-rose-800 border border-rose-200') }}">
                        ● {{ strtoupper(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold font-mono text-slate-900 tracking-tight mt-1">#{{ $invoice->invoice_number }}</h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            
            <!-- CONVERT PROFORMA TO TAX INVOICE -->
            @if($invoice->type === 'proforma')
            <form action="{{ route('invoices.convert-tax', $invoice->id) }}" method="POST" onsubmit="return confirm('Convert this Proforma into an official sequential Tax Invoice?');">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                    <span>⚡ Convert to Tax Invoice</span>
                </button>
            </form>
            @endif

            <!-- 1-CLICK WHATSAPP SHARE -->
            <a href="{{ $whatsappUrl }}" target="_blank" 
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <span>💬 WhatsApp Bill</span>
            </a>

            <!-- COPY PUBLIC CLIENT LINK -->
            <button @click="navigator.clipboard.writeText('{{ $publicUrl }}'); copyNotice = true; setTimeout(() => copyNotice = false, 2500)"
                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <span x-show="!copyNotice">🔗 Public Link</span>
                <span x-show="copyNotice" x-cloak class="text-emerald-600 font-bold">✓ Copied!</span>
            </button>

            <!-- RECORD PAYMENT MODAL TRIGGER -->
            @if($invoice->balance_amount > 0)
            <button @click="recordModalOpen = true" 
                    class="px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <span>💰 Record Payment</span>
            </button>
            @endif

            <!-- PRINT / PDF -->
            <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" 
               class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <span>🖨️ Print / PDF</span>
            </a>

            <!-- EDIT -->
            <a href="{{ route('invoices.edit', $invoice->id) }}" 
               class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-sm transition-colors">
                Edit
            </a>
        </div>
    </div>

    <!-- BALANCE DUE & KPI METRIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Invoiced</span>
            <div class="text-2xl font-black font-mono text-slate-900 mt-1">₹{{ number_format($invoice->total_amount, 2) }}</div>
            <p class="text-xs text-slate-400 mt-0.5">Taxable: ₹{{ number_format($invoice->taxable_amount, 2) }} + Taxes</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Total Collected</span>
            <div class="text-2xl font-black font-mono text-emerald-600 mt-1">₹{{ number_format($invoice->paid_amount, 2) }}</div>
            <p class="text-xs text-slate-400 mt-0.5">{{ $invoice->transactions->count() }} payment transaction(s)</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Remaining Balance</span>
            <div class="text-2xl font-black font-mono text-amber-600 mt-1">₹{{ number_format($invoice->balance_amount, 2) }}</div>
            <p class="text-xs text-slate-400 mt-0.5">Due date: {{ $invoice->due_date ? $invoice->due_date->format('d M, Y') : 'Due on receipt' }}</p>
        </div>
    </div>

    <!-- MAIN INVOICE DETAILS CARD -->
    <div class="p-8 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-6">
        
        <!-- PARTIES INFO -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-100">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Customer Details</span>
                <h3 class="font-bold text-slate-900 text-base mt-1">{{ $invoice->customer->name }}</h3>
                @if($invoice->customer->company_name)
                <p class="text-xs font-medium text-slate-700">{{ $invoice->customer->company_name }}</p>
                @endif
                <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->customer->address }} {{ $invoice->customer->city }} {{ $invoice->customer->state }}</p>
                @if($invoice->customer->phone)
                <p class="text-xs text-slate-500 font-mono mt-1">Phone: {{ $invoice->customer->phone }}</p>
                @endif
                @if($invoice->customer->gstin)
                <p class="text-xs font-mono text-brand-700 mt-0.5">GSTIN: <span class="font-bold">{{ $invoice->customer->gstin }}</span></p>
                @endif
            </div>

            <div class="md:text-right space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Invoice Information</span>
                <div class="text-xs text-slate-600 font-mono">Invoice Date: <strong class="text-slate-900">{{ $invoice->invoice_date->format('d M, Y') }}</strong></div>
                @if($invoice->due_date)
                <div class="text-xs text-slate-600 font-mono">Due Date: <strong class="text-slate-900">{{ $invoice->due_date->format('d M, Y') }}</strong></div>
                @endif
                <div class="text-xs text-slate-600">Tax Calculation: 
                    <span class="font-semibold">{{ $invoice->effective_tax_mode === 'detailed' ? 'Detailed Split (CGST + SGST / IGST)' : 'Simple 18% GST' }}</span>
                </div>
                <div class="text-xs text-slate-600">Sale Type: 
                    <span class="font-semibold">{{ $invoice->sale_type === 'LOCAL' ? 'Intrastate (Local)' : 'Interstate (Central)' }}</span>
                </div>
            </div>
        </div>

        <!-- LINE ITEMS -->
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Billed Items</h4>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Item & Description</th>
                            <th class="py-3 px-3 text-center">HSN/SAC</th>
                            <th class="py-3 px-3 text-right">Qty</th>
                            <th class="py-3 px-3 text-right">Rate</th>
                            <th class="py-3 px-3 text-right">Tax (%)</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900">{{ $item->description }}</div>
                                @if($item->domain_name || $item->service_period_start)
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 mt-1 rounded bg-indigo-50 text-indigo-700 text-[11px] font-mono border border-indigo-100">
                                    @if($item->domain_name)
                                    <span>🌐 {{ $item->domain_name }}</span>
                                    @endif
                                    @if($item->service_period_start)
                                    <span>· Period: {{ $item->service_period_start->format('d M Y') }} to {{ $item->service_period_end ? $item->service_period_end->format('d M Y') : 'Ongoing' }}</span>
                                    @endif
                                    @if($item->billing_cycle)
                                    <span>({{ $item->billing_cycle }})</span>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono text-xs text-slate-500">{{ $item->hsn_sac ?: '-' }}</td>
                            <td class="py-3.5 px-3 text-right font-mono">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="py-3.5 px-3 text-right font-mono">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="py-3.5 px-3 text-right font-mono text-xs text-slate-600">{{ $item->gst_percent }}%</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">₹{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOTALS & ZERO-FEE UPI QR BOX -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <div>
                @if($company->enable_upi_qr && $upiUrl && $invoice->balance_amount > 0)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-4">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($upiUrl) }}" 
                         alt="UPI QR" class="w-24 h-24 rounded-lg shadow-sm border border-slate-200">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Dynamic UPI QR</span>
                        <div class="text-xs font-mono font-bold text-slate-900 mt-1">{{ $company->upi_id }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Scans directly into client banking app with exact balance due: ₹{{ number_format($invoice->balance_amount, 2) }}</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Taxable Value:</span>
                    <span class="font-mono font-semibold">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Total Tax (GST):</span>
                    <span class="font-mono font-semibold">₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between font-black text-lg text-slate-900">
                    <span>Grand Total:</span>
                    <span class="font-mono text-brand-600">₹{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs text-emerald-600 font-semibold">
                    <span>Paid to date:</span>
                    <span class="font-mono">- ₹{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold text-amber-600 pt-1 border-t border-slate-100">
                    <span>Balance Due:</span>
                    <span class="font-mono">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT TRANSACTIONS & RECEIPTS HISTORY -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900">Recorded Payment Transactions</h3>
                <p class="text-xs text-slate-500">History of offline settlements (Cash, Cheque, NEFT) and online gateway payments.</p>
            </div>
            @if($invoice->balance_amount > 0)
            <button @click="recordModalOpen = true" class="px-3.5 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold border border-brand-200 transition-colors">
                + Add Payment
            </button>
            @endif
        </div>

        @if($invoice->transactions->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Method</th>
                        <th class="py-2.5 px-3">Transaction / UTR No</th>
                        <th class="py-2.5 px-3">Notes</th>
                        <th class="py-2.5 px-3 text-right">Amount</th>
                        <th class="py-2.5 px-3 text-center">Receipt</th>
                        <th class="py-2.5 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->transactions as $tx)
                    <tr>
                        <td class="py-3 px-3 font-mono text-slate-700">{{ $tx->paid_at->format('d M Y') }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px] bg-slate-100 text-slate-700">
                                {{ $tx->payment_method ?: $tx->gateway }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono text-slate-600">{{ $tx->transaction_id ?: '-' }}</td>
                        <td class="py-3 px-3 text-slate-500">{{ $tx->notes ?: '-' }}</td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600">₹{{ number_format($tx->amount, 2) }}</td>
                        <td class="py-3 px-3 text-center">
                            <a href="{{ route('payments.receipt', $tx->id) }}" target="_blank" class="text-brand-600 hover:text-brand-800 font-bold underline text-[11px]">
                                🧾 Receipt
                            </a>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <form action="{{ route('payments.destroy', $tx->id) }}" method="POST" onsubmit="return confirm('Cancel and delete this payment record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold">&times; Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-6 text-xs text-slate-400 border-2 border-dashed border-slate-100 rounded-xl">
            No payments recorded yet against this invoice.
        </div>
        @endif
    </div>

    <!-- RECORD PAYMENT MODAL (ALPINE JS) -->
    <div x-show="recordModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
         @click.self="recordModalOpen = false">
        <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base">Record Payment Receipt</h3>
                <button @click="recordModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>

            <form action="{{ route('invoices.payments.store', $invoice->id) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Amount Received (₹) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ $invoice->balance_amount }}" max="{{ $invoice->balance_amount }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 font-mono text-base font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Maximum payable balance: ₹{{ number_format($invoice->balance_amount, 2) }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Payment Method *</label>
                        <select name="payment_method" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm text-slate-800 focus:ring-2 focus:ring-brand-500">
                            <option value="cash">Cash</option>
                            <option value="upi" selected>Direct UPI</option>
                            <option value="neft">Bank NEFT / RTGS</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card Swipe</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Payment Date *</label>
                        <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm text-slate-800 font-mono focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Reference / UTR / Cheque No.</label>
                    <input type="text" name="reference_no" placeholder="e.g. UTR1982736412"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 text-slate-900 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Notes / Remarks</label>
                    <input type="text" name="notes" placeholder="e.g. Received 50% advance via PhonePe"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="recordModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md">
                        Save Payment Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection