<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }} - {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    @if($company->enable_razorpay && $company->razorpay_key_id)
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-shadow-none { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="min-h-full font-sans antialiased text-slate-800 flex flex-col py-8 px-4 sm:px-6 lg:px-8">
    
    <!-- TOP NOTIFICATION BAR -->
    <div class="max-w-4xl mx-auto w-full mb-6 no-print">
        @if(session('success'))
        <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider
                    {{ $invoice->status === 'paid' || $invoice->balance_amount <= 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($invoice->status === 'partially_paid' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-rose-100 text-rose-800 border border-rose-200') }}">
                    ● {{ $invoice->status === 'paid' || $invoice->balance_amount <= 0 ? 'PAID IN FULL' : ($invoice->status === 'partially_paid' ? 'PARTIALLY PAID' : 'UNPAID') }}
                </span>
                <span class="text-xs text-slate-500 font-mono">
                    Balance Due: <strong class="text-slate-900 font-bold">₹{{ number_format($invoice->balance_amount, 2) }}</strong>
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-colors">
                    <span>🖨️</span>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN INVOICE CARD -->
    <div class="max-w-4xl mx-auto w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden print-shadow-none">
        
        <!-- HEADER -->
        <div class="p-8 sm:p-10 border-b border-slate-100 bg-gradient-to-b from-slate-50/50 to-white">
            <div class="flex flex-col sm:flex-row justify-between gap-6">
                <div>
                    @if($company->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="h-12 object-contain mb-3">
                    @endif
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $company->name }}</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm">{{ $company->address }} {{ $company->city }} {{ $company->state }} - {{ $company->pincode }}</p>
                    @if($company->gstin)
                    <p class="text-xs font-mono font-medium text-slate-600 mt-1">GSTIN: <span class="font-bold text-slate-900">{{ $company->gstin }}</span></p>
                    @endif
                    @if($company->email)
                    <p class="text-xs text-slate-500">Email: {{ $company->email }} | Phone: {{ $company->phone }}</p>
                    @endif
                </div>

                <div class="sm:text-right space-y-1">
                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider {{ $invoice->type === 'proforma' ? 'bg-amber-100 text-amber-800' : 'bg-brand-100 text-brand-800' }}">
                        {{ $invoice->type === 'proforma' ? 'PROFORMA INVOICE / ESTIMATE' : 'TAX INVOICE' }}
                    </span>
                    <h1 class="text-2xl font-black font-mono text-slate-900 tracking-tight mt-1">#{{ $invoice->invoice_number }}</h1>
                    <div class="text-xs text-slate-500 space-y-0.5 pt-2 font-mono">
                        <div>Invoice Date: <strong class="text-slate-800">{{ $invoice->invoice_date->format('d M, Y') }}</strong></div>
                        @if($invoice->due_date)
                        <div>Due Date: <strong class="text-slate-800">{{ $invoice->due_date->format('d M, Y') }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BILLED TO -->
            <div class="mt-8 pt-6 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Billed To:</span>
                    <h3 class="font-bold text-slate-900 text-base mt-0.5">{{ $invoice->customer->name }}</h3>
                    @if($invoice->customer->company_name)
                    <p class="text-xs font-semibold text-slate-700">{{ $invoice->customer->company_name }}</p>
                    @endif
                    <p class="text-xs text-slate-500 mt-1">{{ $invoice->customer->address }} {{ $invoice->customer->city }} {{ $invoice->customer->state }}</p>
                    @if($invoice->customer->gstin)
                    <p class="text-xs font-mono font-medium text-slate-600 mt-1">GSTIN: <strong class="text-slate-900">{{ $invoice->customer->gstin }}</strong></p>
                    @endif
                </div>

                <!-- PAYMENT STATUS SUMMARY BOX -->
                <div class="p-4 rounded-xl bg-slate-900 text-white flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Amount</span>
                        <span class="font-mono text-xl font-extrabold text-white">₹{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Paid to Date:</span>
                        <span class="font-mono text-emerald-400 font-bold">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-sm">
                        <span class="text-amber-300 font-bold">Balance Due:</span>
                        <span class="font-mono text-amber-300 font-black text-lg">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- LINE ITEMS TABLE -->
        <div class="p-8 sm:p-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b-2 border-slate-200 text-slate-600 text-xs uppercase font-bold tracking-wider">
                            <th class="py-3 px-2">Item Description</th>
                            <th class="py-3 px-2 text-center">HSN/SAC</th>
                            <th class="py-3 px-2 text-right">Qty</th>
                            <th class="py-3 px-2 text-right">Rate</th>
                            <th class="py-3 px-2 text-right">Tax (%)</th>
                            <th class="py-3 px-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3 px-2">
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
                            <td class="py-3 px-2 text-center font-mono text-xs text-slate-600">{{ $item->hsn_sac ?: '-' }}</td>
                            <td class="py-3 px-2 text-right font-mono">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="py-3 px-2 text-right font-mono">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="py-3 px-2 text-right font-mono text-xs text-slate-600">{{ $item->gst_percent }}%</td>
                            <td class="py-3 px-2 text-right font-mono font-bold text-slate-900">₹{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- TOTALS BREAKDOWN -->
            <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between gap-6">
                <div class="text-xs text-slate-500 space-y-1">
                    @if($company->bank_name)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <strong class="text-slate-800 block text-xs mb-1">Direct Bank Wire / NEFT Transfer:</strong>
                        <div>Bank: <span class="font-mono font-bold text-slate-900">{{ $company->bank_name }}</span></div>
                        <div>Account No: <span class="font-mono font-bold text-slate-900">{{ $company->bank_account_number }}</span></div>
                        <div>IFSC: <span class="font-mono font-bold text-slate-900">{{ $company->bank_ifsc }}</span></div>
                        @if($company->bank_branch)
                        <div>Branch: {{ $company->bank_branch }}</div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="sm:w-72 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Taxable Value:</span>
                        <span class="font-mono font-semibold">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                    </div>

                    @if($invoice->effective_tax_mode === 'simple')
                    <div class="flex justify-between text-slate-600">
                        <span>GST Total:</span>
                        <span class="font-mono font-semibold">₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</span>
                    </div>
                    @else
                        @if($invoice->cgst_amount > 0)
                        <div class="flex justify-between text-slate-500 text-xs">
                            <span>CGST:</span>
                            <span class="font-mono">₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                        </div>
                        @endif
                        @if($invoice->sgst_amount > 0)
                        <div class="flex justify-between text-slate-500 text-xs">
                            <span>SGST:</span>
                            <span class="font-mono">₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                        </div>
                        @endif
                        @if($invoice->igst_amount > 0)
                        <div class="flex justify-between text-slate-500 text-xs">
                            <span>IGST:</span>
                            <span class="font-mono">₹{{ number_format($invoice->igst_amount, 2) }}</span>
                        </div>
                        @endif
                    @endif

                    <div class="pt-2 border-t border-slate-200 flex justify-between font-extrabold text-base text-slate-900">
                        <span>Total:</span>
                        <span class="font-mono text-brand-600">₹{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>

                    @if($invoice->paid_amount > 0)
                    <div class="flex justify-between text-xs text-emerald-600 font-semibold">
                        <span>Paid:</span>
                        <span class="font-mono">- ₹{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-amber-600 pt-1 border-t border-slate-100">
                        <span>Balance Due:</span>
                        <span class="font-mono">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- PAYMENT ACTIONS BOX (IF UNPAID / BALANCE DUE) -->
        @if($invoice->balance_amount > 0)
        <div class="p-8 bg-slate-900 text-white rounded-b-3xl border-t border-slate-800 no-print">
            <div class="max-w-2xl mx-auto text-center space-y-4">
                <div>
                    <h3 class="text-lg font-bold">Complete Payment for Invoice #{{ $invoice->invoice_number }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Amount to pay: <strong class="text-amber-400 font-mono text-sm">₹{{ number_format($invoice->balance_amount, 2) }}</strong></p>
                </div>

                <div class="grid grid-cols-1 {{ ($company->enable_upi_qr && $upiUrl) && ($company->enable_razorpay && $company->razorpay_key_id) ? 'sm:grid-cols-2' : '' }} gap-6 pt-2">
                    
                    <!-- OPTION 1: INSTANT DYNAMIC ZERO-FEE UPI QR CODE -->
                    @if($company->enable_upi_qr && $upiUrl)
                    <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700 flex flex-col items-center text-center">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 mb-2">⚡ Scan & Pay via any UPI App</span>
                        <div class="p-2.5 bg-white rounded-xl shadow-lg mb-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($upiUrl) }}" 
                                 alt="UPI Payment QR" class="w-36 h-36">
                        </div>
                        <p class="text-[11px] text-slate-300 font-mono font-bold">{{ $company->upi_id }}</p>
                        <p class="text-[10px] text-slate-400 mt-1">Google Pay · PhonePe · Paytm · CRED · BHIM</p>
                        <a href="{{ $upiUrl }}" class="mt-3 w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all sm:hidden">
                            📱 Tap to Pay on Mobile
                        </a>
                    </div>
                    @endif

                    <!-- OPTION 2: DEDICATED RAZORPAY ONLINE GATEWAY -->
                    @if($company->enable_razorpay && $company->razorpay_key_id)
                    <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700 flex flex-col justify-between items-center text-center">
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-400">💳 Online Payment Gateway</span>
                            <h4 class="font-bold text-sm text-white">Credit / Debit Card, NetBanking & Wallets</h4>
                            <p class="text-xs text-slate-400">Instant online receipt and automated invoice reconciliation.</p>
                        </div>

                        <button id="rzp-button" class="mt-4 w-full py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg transition-all flex items-center justify-center gap-2">
                            <span>🔒 Pay ₹{{ number_format($invoice->balance_amount, 2) }} Online</span>
                        </button>
                    </div>

                    <script>
                        var options = {
                            "key": "{{ $company->razorpay_key_id }}",
                            "amount": "{{ round($invoice->balance_amount * 100) }}",
                            "currency": "INR",
                            "name": "{{ $company->name }}",
                            "description": "Invoice #{{ $invoice->invoice_number }}",
                            @if($company->logo_path)
                            "image": "{{ asset('storage/' . $company->logo_path) }}",
                            @endif
                            "handler": function (response){
                                // Post back to server for verification and status update
                                var form = document.createElement('form');
                                form.method = 'POST';
                                form.action = "{{ route('public.invoice.razorpay', $invoice->public_uuid) }}";
                                
                                var token = document.createElement('input');
                                token.type = 'hidden';
                                token.name = '_token';
                                token.value = "{{ csrf_token() }}";
                                form.appendChild(token);

                                var pid = document.createElement('input');
                                pid.type = 'hidden';
                                pid.name = 'razorpay_payment_id';
                                pid.value = response.razorpay_payment_id;
                                form.appendChild(pid);

                                document.body.appendChild(form);
                                form.submit();
                            },
                            "prefill": {
                                "name": "{{ $invoice->customer->name }}",
                                "email": "{{ $invoice->customer->email }}",
                                "contact": "{{ $invoice->customer->phone }}"
                            },
                            "theme": {
                                "color": "#4f46e5"
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        document.getElementById('rzp-button').onclick = function(e){
                            rzp1.open();
                            e.preventDefault();
                        }
                    </script>
                    @endif

                </div>
            </div>
        </div>
        @else
        <!-- FULLY PAID BADGE -->
        <div class="p-6 bg-emerald-950/40 text-emerald-400 border-t border-emerald-900/40 text-center rounded-b-3xl">
            <span class="text-sm font-bold flex items-center justify-center gap-2">
                <span>🎉</span>
                <span>This invoice is fully settled and paid. Thank you for your prompt business!</span>
            </span>
        </div>
        @endif

    </div>

    <!-- FOOTER -->
    <div class="mt-8 text-center text-xs text-slate-400 no-print">
        Powered by <strong>{{ $company->name }} Billing System</strong> · Secured with End-to-End Encryption
    </div>

</body>
</html>