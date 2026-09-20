<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - {{ $transaction->transaction_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        @page { size: A5 landscape; margin: 10mm; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 flex justify-center items-center min-h-screen">
    
    <div class="max-w-2xl w-full bg-white p-8 rounded-2xl shadow-xl border border-slate-200 space-y-6">
        
        <!-- HEADER -->
        <div class="flex justify-between items-start border-b pb-4 border-slate-200">
            <div>
                <h1 class="text-xl font-black text-slate-900">{{ $invoice->company->name }}</h1>
                <p class="text-xs text-slate-500">{{ $invoice->company->address }} {{ $invoice->company->city }} {{ $invoice->company->state }}</p>
                @if($invoice->company->gstin)
                <p class="text-xs font-mono font-bold text-slate-700">GSTIN: {{ $invoice->company->gstin }}</p>
                @endif
            </div>

            <div class="text-right">
                <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold uppercase tracking-wider">
                    PAYMENT RECEIPT
                </span>
                <p class="text-xs font-mono text-slate-500 mt-2">Receipt No: <strong>#REC-{{ $transaction->id }}</strong></p>
                <p class="text-xs font-mono text-slate-500">Date: <strong>{{ $transaction->paid_at->format('d M, Y') }}</strong></p>
            </div>
        </div>

        <!-- DETAILS -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 uppercase font-bold text-[10px] block">Received From:</span>
                <strong class="text-sm text-slate-900 block mt-0.5">{{ $invoice->customer->name }}</strong>
                @if($invoice->customer->company_name)
                <p class="text-slate-600">{{ $invoice->customer->company_name }}</p>
                @endif
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 uppercase font-bold text-[10px] block">Applied To:</span>
                <strong class="text-sm text-slate-900 block mt-0.5">Invoice #{{ $invoice->invoice_number }}</strong>
                <p class="text-slate-600 font-mono">Invoice Date: {{ $invoice->invoice_date->format('d M, Y') }}</p>
            </div>
        </div>

        <!-- TRANSACTION INFO -->
        <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 block">Payment Mode: <strong class="text-slate-800 uppercase">{{ $transaction->payment_method ?: $transaction->gateway }}</strong></span>
                <span class="text-xs text-slate-500 block font-mono">Transaction Ref / UTR: <strong class="text-slate-800">{{ $transaction->transaction_id }}</strong></span>
                @if($transaction->notes)
                <span class="text-xs text-slate-500 block italic mt-0.5">Note: {{ $transaction->notes }}</span>
                @endif
            </div>

            <div class="text-right">
                <span class="text-xs text-emerald-700 font-bold uppercase tracking-wider block">Amount Received</span>
                <span class="text-2xl font-black font-mono text-emerald-700">₹{{ number_format($transaction->amount, 2) }}</span>
            </div>
        </div>

        <!-- INVOICE BALANCE AFTER TRANSACTION -->
        <div class="flex justify-between items-center text-xs text-slate-500 pt-2 border-t border-slate-100">
            <div>Invoice Total: <strong class="text-slate-800">₹{{ number_format($invoice->total_amount, 2) }}</strong></div>
            <div>Total Paid: <strong class="text-emerald-700">₹{{ number_format($invoice->paid_amount, 2) }}</strong></div>
            <div>Remaining Balance: <strong class="text-amber-600">₹{{ number_format($invoice->balance_amount, 2) }}</strong></div>
        </div>

        <!-- SIGNATURE -->
        <div class="pt-6 flex justify-between items-end text-xs">
            <div class="text-[11px] text-slate-400">
                This is a computer-generated receipt voucher.
            </div>
            <div class="text-center">
                <div class="w-36 border-b border-slate-300 pb-8"></div>
                <span class="text-[11px] font-bold text-slate-700 mt-1 block">Authorized Signatory</span>
            </div>
        </div>

        <!-- BUTTON -->
        <div class="no-print pt-4 flex justify-end">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 shadow-md">
                🖨️ Print Receipt Voucher
            </button>
        </div>

    </div>

</body>
</html>