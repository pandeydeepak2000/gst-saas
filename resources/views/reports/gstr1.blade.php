@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>GSTR-1 Tax Reports & CA Filing Center</span>
                <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Accountant Ready</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Automatic grouping into Table 4 (B2B), Table 5/7 (B2C), and Table 12 (HSN Summary).</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- MONTH FILTER -->
            <form action="{{ route('reports.gstr1') }}" method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                       class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono text-slate-800 focus:ring-2 focus:ring-brand-500">
            </form>

            <!-- 1-CLICK EXPORT CSV -->
            <a href="{{ route('reports.gstr1.export', ['month' => $month]) }}" 
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition-all flex items-center gap-1.5">
                <span>📥 Export GSTR-1 CSV</span>
            </a>
        </div>
    </div>

    <!-- SUMMARY METRICS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Taxable Value</span>
            <div class="text-xl font-black font-mono text-slate-900 mt-1">₹{{ number_format($metrics['total_taxable'], 2) }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $metrics['total_invoices'] }} Invoices in {{ date('F Y', strtotime($month . '-01')) }}</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600">Total Tax (CGST+SGST+IGST)</span>
            <div class="text-xl font-black font-mono text-brand-600 mt-1">₹{{ number_format($metrics['total_cgst'] + $metrics['total_sgst'] + $metrics['total_igst'], 2) }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">CGST: ₹{{ number_format($metrics['total_cgst'], 2) }} | SGST: ₹{{ number_format($metrics['total_sgst'], 2) }}</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Table 4: B2B Volume</span>
            <div class="text-xl font-black font-mono text-indigo-600 mt-1">{{ $metrics['b2b_count'] }} Bills</div>
            <p class="text-[11px] text-slate-400 mt-0.5">₹{{ number_format($metrics['b2b_taxable'], 2) }} Taxable</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-purple-600">Table 5/7: B2C Volume</span>
            <div class="text-xl font-black font-mono text-purple-600 mt-1">{{ $metrics['b2c_count'] }} Bills</div>
            <p class="text-[11px] text-slate-400 mt-0.5">₹{{ number_format($metrics['b2c_taxable'], 2) }} Taxable</p>
        </div>
    </div>

    <!-- TABLE 4: B2B INVOICES (TAXABLE SUPPLIES TO REGISTERED PERSONS) -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>Table 4: B2B Tax Invoices</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">{{ $b2bInvoices->count() }} Records</span>
                </h3>
                <p class="text-xs text-slate-500">Sales made to GST-registered businesses (Input Tax Credit eligible).</p>
            </div>
        </div>

        @if($b2bInvoices->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Invoice No</th>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Customer GSTIN</th>
                        <th class="py-2.5 px-3">Party Name</th>
                        <th class="py-2.5 px-3 text-right">Taxable</th>
                        <th class="py-2.5 px-3 text-right">CGST</th>
                        <th class="py-2.5 px-3 text-right">SGST</th>
                        <th class="py-2.5 px-3 text-right">IGST</th>
                        <th class="py-2.5 px-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($b2bInvoices as $inv)
                    <tr>
                        <td class="py-2.5 px-3 font-mono font-bold text-brand-700">
                            <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">#{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="py-2.5 px-3 font-mono text-slate-600">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $inv->customer->gstin }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $inv->customer->name }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-semibold">₹{{ number_format($inv->taxable_amount, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-600">₹{{ number_format($inv->cgst_amount, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-600">₹{{ number_format($inv->sgst_amount, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-600">₹{{ number_format($inv->igst_amount, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">₹{{ number_format($inv->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-6 text-xs text-slate-400 border-2 border-dashed border-slate-100 rounded-xl">
            No B2B invoices generated in this period.
        </div>
        @endif
    </div>

    <!-- TABLE 12: HSN / SAC CODE SUMMARY -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>Table 12: HSN / SAC Summary of Outward Supplies</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Required by GSTN</span>
                </h3>
                <p class="text-xs text-slate-500">Totals aggregated by HSN / SAC tariff codes for direct GST portal paste.</p>
            </div>
        </div>

        @if($hsnSummary->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">HSN / SAC</th>
                        <th class="py-2.5 px-3">Description</th>
                        <th class="py-2.5 px-3 text-right">Total Qty</th>
                        <th class="py-2.5 px-3 text-right">Taxable Value</th>
                        <th class="py-2.5 px-3 text-right">CGST</th>
                        <th class="py-2.5 px-3 text-right">SGST</th>
                        <th class="py-2.5 px-3 text-right">IGST</th>
                        <th class="py-2.5 px-3 text-right">Total Tax Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($hsnSummary as $hsn)
                    <tr>
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $hsn->hsn_sac ?: 'N/A' }}</td>
                        <td class="py-2.5 px-3 text-slate-700">{{ $hsn->description ?: 'General Supply' }}</td>
                        <td class="py-2.5 px-3 text-right font-mono">{{ $hsn->total_qty }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-900">₹{{ number_format($hsn->total_taxable, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-600">₹{{ number_format($hsn->total_cgst, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-600">₹{{ number_format($hsn->total_sgst, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-600">₹{{ number_format($hsn->total_igst, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600">₹{{ number_format($hsn->total_cgst + $hsn->total_sgst + $hsn->total_igst, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-6 text-xs text-slate-400 border-2 border-dashed border-slate-100 rounded-xl">
            No HSN line items found for this period.
        </div>
        @endif
    </div>

</div>
@endsection