<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice_{{ $invoice->invoice_number }} - {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @page {
            size: A4 portrait;
            margin: 0mm !important;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            background: #f1f5f9;
            font-size: 11px;
            line-height: 1.4;
            padding-bottom: 30px;
        }
        
        /* Interactive Control Toolbar (Excluded from Print) */
        .toolbar {
            background: #0f172a;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 12px;
        }
        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-tab {
            padding: 5px 12px;
            border-radius: 6px;
            border: 1px solid #334155;
            background: #1e293b;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-tab.active {
            background: #4f46e5;
            color: #fff;
            border-color: #6366f1;
        }
        .toolbar-checkbox {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-size: 11px;
            color: #cbd5e1;
        }
        .btn-print {
            padding: 7px 18px;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(16,185,129,0.3);
            transition: background 0.2s;
        }
        .btn-print:hover {
            background: #059669;
        }

        /* Natural Single-Page A4 Container - Zero Artificial Gap */
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 12mm 15mm 12mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }
            .toolbar, .no-print {
                display: none !important;
            }
            .page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: 100vh !important;
                padding: 10mm 14mm !important;
            }
        }
    </style>
</head>
<body x-data="{ 
    template: '{{ request('template', $company->invoice_design_template ?? 'modern') }}',
    showBank: {{ ($company->show_bank_on_invoice ?? true) ? 'true' : 'false' }},
    showQr: {{ (($company->show_qr_on_invoice ?? true) && $company->enable_upi_qr && !empty($company->upi_id)) ? 'true' : 'false' }},
    showNotes: true
}">

<!-- 1. FLOATING CONTROL TOOLBAR (HIDDEN ON PRINT) -->
<div class="toolbar no-print">
    <div class="toolbar-group">
        <span style="font-weight: 800; color: #fff;">Invoice Design:</span>
        <button type="button" class="btn-tab" :class="template === 'modern' ? 'active' : ''" @click="template = 'modern'">
            ✨ Template 1: Modern Executive
        </button>
        <button type="button" class="btn-tab" :class="template === 'classic' ? 'active' : ''" @click="template = 'classic'">
            📐 Template 2: Classic Corporate
        </button>
    </div>

    <div class="toolbar-group">
        <span style="font-weight: 800; color: #94a3b8; font-size: 11px;">Visibility:</span>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showBank">
            <span>Bank Wire Details</span>
        </label>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showQr">
            <span>Payment QR Code</span>
        </label>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showNotes">
            <span>Notes & Terms</span>
        </label>
    </div>

    <div>
        <button class="btn-print" onclick="window.print()">
            <span>🖨️ Print / Save PDF</span>
        </button>
    </div>
</div>

<!-- 2. INVOICE DOCUMENT CONTAINER -->
<div class="page">
    
    <!-- ======================================================== -->
    <!-- TEMPLATE 1: MODERN EXECUTIVE (CLEAN INDIGO ACCENT)      -->
    <!-- ======================================================== -->
    <div x-show="template === 'modern'" style="display: flex; flex-direction: column; gap: 12px; width: 100%;">
        
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;">
            <div>
                @if($company->logo_path)
                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" style="max-height: 40px; margin-bottom: 4px;">
                @endif
                <span style="font-size: 8.5px; font-weight: 800; letter-spacing: 1px; color: #4f46e5; text-transform: uppercase;">
                    {{ $invoice->type === 'proforma' ? 'PROFORMA INVOICE / ESTIMATE' : 'TAX INVOICE' }}
                </span>
                <h1 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 1px;">{{ $company->name }}</h1>
                <p style="color: #64748b; font-size: 9.5px; max-width: 320px; line-height: 1.3; margin-top: 2px;">
                    {{ $company->address ?: 'Corporate Office' }}, {{ $company->city }}, {{ $company->state }} {{ $company->pincode ? '- ' . $company->pincode : '' }}
                </p>
                <div style="font-size: 9.5px; color: #334155; margin-top: 3px;" class="mono">
                    @if($company->gstin)<div>GSTIN: <strong>{{ $company->gstin }}</strong></div>@endif
                    @if($company->phone)<div>Phone: {{ $company->phone }}</div>@endif
                </div>
            </div>

            <div style="text-align: right;">
                <div class="mono" style="font-size: 17px; font-weight: 800; color: #4f46e5;">#{{ $invoice->invoice_number }}</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">
                    Date: <strong style="color: #0f172a;">{{ $invoice->invoice_date->format('d M, Y') }}</strong>
                </div>
                @if($invoice->due_date)
                <div style="font-size: 9.5px; color: #64748b;">
                    Due Date: <strong style="color: #0f172a;">{{ $invoice->due_date->format('d M, Y') }}</strong>
                </div>
                @endif
                <div style="margin-top: 5px;">
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; background: {{ $invoice->status === 'paid' ? '#dcfce7; color: #166534;' : ($invoice->status === 'partially_paid' ? '#e0e7ff; color: #3730a3;' : '#fef3c7; color: #92400e;') }}">
                        ● {{ strtoupper(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Parties Box -->
        <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 12px;">
            <div style="width: 58%;">
                <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">BILLED TO:</span>
                <div style="font-size: 11.5px; font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $invoice->customer->name ?? 'Walk-in Customer' }}</div>
                @if($invoice->customer && $invoice->customer->company_name)
                    <div style="font-size: 9.5px; color: #475569; font-weight: 600;">{{ $invoice->customer->company_name }}</div>
                @endif
                <div style="font-size: 9.5px; color: #64748b; margin-top: 2px; line-height: 1.3;">{{ $invoice->customer->address ?? '' }} {{ $invoice->customer->city ?? '' }} {{ $invoice->customer->state ?? '' }}</div>
                @if($invoice->customer && $invoice->customer->gstin)
                    <div class="mono" style="font-size: 9.5px; color: #1e293b; margin-top: 2px;">GSTIN: <strong style="color: #4f46e5;">{{ $invoice->customer->gstin }}</strong></div>
                @endif
            </div>

            <div style="width: 38%; text-align: right; font-size: 9.5px;">
                <div style="color: #64748b; font-weight: 600;">PLACE OF SUPPLY:</div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 1px;">
                    {{ $invoice->sale_type === 'LOCAL' ? 'Intra-State (' . $company->state . ')' : 'Inter-State (Central IGST)' }}
                </div>
                <div style="margin-top: 4px; color: #64748b;">
                    Tax Mode: <strong style="color: #0f172a;">{{ $invoice->effective_tax_mode === 'detailed' ? 'Detailed Split' : 'Simple 18% GST' }}</strong>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table style="border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
            <thead>
                <tr style="background: #0f172a; color: #fff;">
                    <th style="width: 25px; text-align: center; padding: 6px;">#</th>
                    <th style="text-align: left; padding: 6px 8px;">Item Description</th>
                    <th style="width: 50px; text-align: center; padding: 6px;">HSN/SAC</th>
                    <th style="width: 45px; text-align: center; padding: 6px;">Qty</th>
                    <th style="width: 65px; text-align: right; padding: 6px;">Rate</th>
                    <th style="width: 45px; text-align: center; padding: 6px;">Tax %</th>
                    <th style="width: 75px; text-align: right; padding: 6px 8px;">Amount</th>
                </tr>
            </thead>
            <tbody class="mono" style="font-size: 9.5px;">
                @foreach($invoice->items as $idx => $item)
                <tr style="border-bottom: 1px solid #e2e8f0; {{ $idx % 2 === 1 ? 'background: #f8fafc;' : '' }}">
                    <td style="text-align: center; color: #94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 500; color: #0f172a;">
                        <div>{{ $item->description }}</div>
                        @if(!empty($item->domain_name) || !empty($item->service_period_start))
                        <div style="font-size: 8px; color: #4338ca; margin-top: 2px; font-weight: 600;">
                            @if(!empty($item->domain_name))
                                <span style="background: #e0e7ff; padding: 1px 4px; border-radius: 3px; color: #3730a3;">🌐 {{ $item->domain_name }}</span>
                            @endif
                            @if(!empty($item->service_period_start))
                                <span style="color: #64748b; margin-left: 3px;">Period: {{ \Carbon\Carbon::parse($item->service_period_start)->format('d M Y') }} to {{ \Carbon\Carbon::parse($item->service_period_end)->format('d M Y') }}</span>
                            @endif
                            @if(!empty($item->billing_cycle))
                                <span style="background: #f1f5f9; padding: 1px 4px; border-radius: 3px; color: #475569; margin-left: 2px;">({{ $item->billing_cycle }})</span>
                            @endif
                        </div>
                        @endif
                    </td>
                    <td style="text-align: center; color: #64748b;">{{ $item->hsn_sac ?: '-' }}</td>
                    <td style="text-align: center;">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td style="text-align: right;">₹{{ number_format($item->rate, 2) }}</td>
                    <td style="text-align: center; font-weight: 600;">{{ $item->gst_percent }}%</td>
                    <td style="text-align: right; font-weight: 700; color: #0f172a;">₹{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals & Notes Section (Natural Compact Spacing) -->
        <div style="display: flex; justify-content: space-between; gap: 14px;">
            <div style="width: 55%; font-size: 9px; color: #64748b;" x-show="showNotes">
                @if($invoice->notes)
                    <strong style="color: #0f172a; display: block; margin-bottom: 2px;">Notes / Remarks:</strong>
                    <div style="white-space: pre-line; background: #f8fafc; padding: 5px 8px; border-radius: 4px; border: 1px solid #e2e8f0; margin-bottom: 6px;">{{ $invoice->notes }}</div>
                @endif
                <div>
                    <strong style="color: #0f172a;">Terms & Conditions:</strong>
                    <div style="margin-top: 1px;">1. Payment due within specified credit period. 2. Goods/services once sold are subject to local jurisdiction.</div>
                </div>
            </div>

            <div style="width: 42%; font-size: 10px;" class="mono">
                <div style="display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px dashed #cbd5e1;">
                    <span style="color: #64748b;">Taxable Value:</span>
                    <span style="font-weight: 600;">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                </div>

                @if($invoice->effective_tax_mode === 'simple')
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #cbd5e1; color: #4f46e5; font-weight: 700;">
                        <span>GST Total:</span>
                        <span>₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</span>
                    </div>
                @else
                    @if($invoice->sale_type === 'LOCAL')
                        <div style="display: flex; justify-content: space-between; padding: 2px 0; color: #059669;">
                            <span>CGST:</span>
                            <span>₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 2px 0; border-bottom: 1px dashed #cbd5e1; color: #059669;">
                            <span>SGST:</span>
                            <span>₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                        </div>
                    @else
                        <div style="display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px dashed #cbd5e1; color: #7c3aed;">
                            <span>IGST (Integrated):</span>
                            <span>₹{{ number_format($invoice->igst_amount, 2) }}</span>
                        </div>
                    @endif
                @endif

                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-top: 2px solid #0f172a; font-size: 12.5px; font-weight: 800; color: #0f172a;">
                    <span>Grand Total:</span>
                    <span style="color: #4f46e5;">₹{{ number_format($invoice->total_amount, 2) }}</span>
                </div>

                @if($invoice->paid_amount > 0)
                <div style="display: flex; justify-content: space-between; padding: 2px 0; font-size: 9.5px; color: #166534; font-weight: 600;">
                    <span>Paid to Date:</span>
                    <span>- ₹{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 2px 0; font-size: 11px; font-weight: 800; color: #b45309; border-top: 1px solid #e2e8f0;">
                    <span>Balance Due:</span>
                    <span>₹{{ number_format($invoice->balance_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Natural Settlement Footer (Compact, No 15cm void!) -->
        <div style="border-top: 1.5px solid #0f172a; padding-top: 10px; margin-top: 6px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                
                <!-- Bank Info (Toggleable) -->
                <div style="font-size: 9px; color: #334155; line-height: 1.4;" x-show="showBank">
                    <strong style="color: #0f172a; font-size: 9.5px; text-transform: uppercase;">Bank Wire Details:</strong>
                    <div class="mono" style="margin-top: 2px;">
                        @if($company->bank_name)<div>Bank: <strong>{{ $company->bank_name }}</strong></div>@endif
                        @if($company->bank_account_number)<div>A/C No: <strong>{{ $company->bank_account_number }}</strong></div>@endif
                        @if($company->bank_ifsc)<div>IFSC: <strong>{{ $company->bank_ifsc }}</strong></div>@endif
                    </div>
                </div>

                <!-- QR Code (Toggleable) -->
                <div style="text-align: center;" x-show="showQr && '{{ $company->upi_id }}' !== ''">
                    @if($company->upi_id)
                    @php
                        $upiPayload = "upi://pay?pa=" . urlencode($company->upi_id) . "&pn=" . urlencode($company->name) . "&am=" . $invoice->balance_amount . "&cu=INR&tn=" . urlencode("Invoice " . $invoice->invoice_number);
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=" . urlencode($upiPayload);
                    @endphp
                    <img src="{{ $qrUrl }}" alt="UPI QR" style="width: 65px; height: 65px; border: 1px solid #cbd5e1; padding: 2px; border-radius: 4px; display: inline-block;">
                    <div style="font-size: 7.5px; color: #64748b; margin-top: 1px; font-weight: 700;">UPI: {{ $company->upi_id }}</div>
                    @endif
                </div>

                <!-- Authorized Signatory -->
                <div style="text-align: right; min-width: 140px;">
                    <div style="font-size: 8.5px; color: #64748b; text-transform: uppercase;">For {{ $company->name }}</div>
                    <div style="height: 35px;"></div>
                    <div style="border-top: 1px solid #94a3b8; padding-top: 2px; font-weight: 700; font-size: 9px; color: #0f172a;">
                        Authorized Signatory
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TEMPLATE 2: CLASSIC CORPORATE (MONOCHROME & CRISP BORDERS)-->
    <!-- ======================================================== -->
    <div x-show="template === 'classic'" x-cloak style="display: flex; flex-direction: column; gap: 10px; width: 100%;">
        
        <!-- Classic Header -->
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px;">
            <span style="font-size: 10px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;">
                {{ $invoice->type === 'proforma' ? 'PROFORMA INVOICE' : 'TAX INVOICE' }}
            </span>
            <h1 style="font-size: 20px; font-weight: 900; color: #000; text-transform: uppercase; margin-top: 2px;">{{ $company->name }}</h1>
            <p style="font-size: 9px; color: #333;">
                {{ $company->address }}, {{ $company->city }}, {{ $company->state }} {{ $company->pincode ? '- ' . $company->pincode : '' }}
                @if($company->gstin) | GSTIN: <strong>{{ $company->gstin }}</strong> @endif
                @if($company->phone) | Phone: {{ $company->phone }} @endif
            </p>
        </div>

        <!-- Classic Two Column Box -->
        <table style="border: 1px solid #000; font-size: 9.5px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border-right: 1px solid #000; padding: 6px 8px;">
                    <div style="font-weight: 800; text-transform: uppercase; font-size: 8.5px; color: #444;">Details of Receiver (Billed To):</div>
                    <div style="font-size: 12px; font-weight: 800; color: #000; margin-top: 2px;">{{ $invoice->customer->name }}</div>
                    @if($invoice->customer && $invoice->customer->company_name)
                    <div style="font-weight: 600;">{{ $invoice->customer->company_name }}</div>
                    @endif
                    <div style="color: #444;">{{ $invoice->customer->address }} {{ $invoice->customer->city }} {{ $invoice->customer->state }}</div>
                    @if($invoice->customer && $invoice->customer->gstin)
                    <div class="mono" style="margin-top: 2px;">GSTIN / UIN: <strong>{{ $invoice->customer->gstin }}</strong></div>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top; padding: 6px 8px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Invoice No:</span>
                        <strong class="mono" style="font-size: 11px;">{{ $invoice->invoice_number }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                        <span>Invoice Date:</span>
                        <strong>{{ $invoice->invoice_date->format('d/m/Y') }}</strong>
                    </div>
                    @if($invoice->due_date)
                    <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                        <span>Payment Due Date:</span>
                        <strong>{{ $invoice->due_date->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                        <span>Place of Supply:</span>
                        <strong>{{ $invoice->sale_type === 'LOCAL' ? $company->state : 'Interstate Supply' }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Classic Bordered Table -->
        <table style="border: 1px solid #000; font-size: 9.5px;">
            <thead>
                <tr style="background: #e2e8f0; border-bottom: 1px solid #000; font-weight: 800; text-transform: uppercase;">
                    <th style="width: 25px; text-align: center; border-right: 1px solid #000; padding: 5px; color: #000; background: #e2e8f0;">#</th>
                    <th style="text-align: left; border-right: 1px solid #000; padding: 5px 8px; color: #000; background: #e2e8f0;">Description of Goods / Services</th>
                    <th style="width: 50px; text-align: center; border-right: 1px solid #000; padding: 5px; color: #000; background: #e2e8f0;">HSN</th>
                    <th style="width: 45px; text-align: center; border-right: 1px solid #000; padding: 5px; color: #000; background: #e2e8f0;">Qty</th>
                    <th style="width: 65px; text-align: right; border-right: 1px solid #000; padding: 5px; color: #000; background: #e2e8f0;">Rate</th>
                    <th style="width: 45px; text-align: center; border-right: 1px solid #000; padding: 5px; color: #000; background: #e2e8f0;">Tax %</th>
                    <th style="width: 80px; text-align: right; padding: 5px 8px; color: #000; background: #e2e8f0;">Amount (INR)</th>
                </tr>
            </thead>
            <tbody class="mono">
                @foreach($invoice->items as $idx => $item)
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td style="text-align: center; border-right: 1px solid #cbd5e1; padding: 5px;">{{ $idx + 1 }}</td>
                    <td style="border-right: 1px solid #cbd5e1; padding: 5px 8px; font-family: 'Plus Jakarta Sans', sans-serif;">
                        <strong>{{ $item->description }}</strong>
                        @if($item->domain_name)
                            <div style="font-size: 8px; color: #444;">Asset: {{ $item->domain_name }} ({{ $item->billing_cycle }})</div>
                        @endif
                    </td>
                    <td style="text-align: center; border-right: 1px solid #cbd5e1; padding: 5px;">{{ $item->hsn_sac ?: '-' }}</td>
                    <td style="text-align: center; border-right: 1px solid #cbd5e1; padding: 5px;">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td style="text-align: right; border-right: 1px solid #cbd5e1; padding: 5px;">₹{{ number_format($item->rate, 2) }}</td>
                    <td style="text-align: center; border-right: 1px solid #cbd5e1; padding: 5px;">{{ $item->gst_percent }}%</td>
                    <td style="text-align: right; padding: 5px 8px; font-weight: 700;">₹{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Classic Totals & Compact Grid -->
        <table style="border: 1px solid #000; font-size: 9.5px;">
            <tr>
                <td style="width: 55%; vertical-align: top; border-right: 1px solid #000; padding: 6px 8px;">
                    <div x-show="showBank">
                        <strong style="text-transform: uppercase; font-size: 8.5px; display: block; border-bottom: 1px solid #ccc; padding-bottom: 2px;">Bank Wire Remittance:</strong>
                        <div class="mono" style="margin-top: 3px; font-size: 9px; line-height: 1.4;">
                            @if($company->bank_name)<div>Bank: {{ $company->bank_name }}</div>@endif
                            @if($company->bank_account_number)<div>A/C: {{ $company->bank_account_number }}</div>@endif
                            @if($company->bank_ifsc)<div>IFSC: {{ $company->bank_ifsc }}</div>@endif
                        </div>
                    </div>

                    <div style="margin-top: 6px;" x-show="showNotes && '{{ $invoice->notes }}' !== ''">
                        <strong style="font-size: 8.5px;">Remarks:</strong>
                        <div style="color: #444;">{{ $invoice->notes }}</div>
                    </div>
                </td>
                <td style="width: 45%; vertical-align: top; padding: 0;">
                    <table class="mono" style="font-size: 9.5px; width: 100%;">
                        <tr style="border-bottom: 1px solid #cbd5e1;">
                            <td style="padding: 4px 8px;">Taxable Amount:</td>
                            <td style="text-align: right; padding: 4px 8px;">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
                        </tr>
                        @if($invoice->effective_tax_mode === 'simple')
                        <tr style="border-bottom: 1px solid #cbd5e1;">
                            <td style="padding: 4px 8px;">GST (18%):</td>
                            <td style="text-align: right; padding: 4px 8px;">₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</td>
                        </tr>
                        @else
                            @if($invoice->sale_type === 'LOCAL')
                            <tr><td style="padding: 2px 8px;">CGST:</td><td style="text-align: right; padding: 2px 8px;">₹{{ number_format($invoice->cgst_amount, 2) }}</td></tr>
                            <tr style="border-bottom: 1px solid #cbd5e1;"><td style="padding: 2px 8px;">SGST:</td><td style="text-align: right; padding: 2px 8px;">₹{{ number_format($invoice->sgst_amount, 2) }}</td></tr>
                            @else
                            <tr style="border-bottom: 1px solid #cbd5e1;"><td style="padding: 4px 8px;">IGST:</td><td style="text-align: right; padding: 4px 8px;">₹{{ number_format($invoice->igst_amount, 2) }}</td></tr>
                            @endif
                        @endif
                        <tr style="border-top: 1px solid #000; font-weight: 900; background: #f1f5f9; font-size: 11px;">
                            <td style="padding: 6px 8px;">Grand Total:</td>
                            <td style="text-align: right; padding: 6px 8px;">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Classic Footer with Signature & Optional QR -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 6px;">
            <div x-show="showQr && '{{ $company->upi_id }}' !== ''">
                @if($company->upi_id)
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ urlencode("upi://pay?pa=" . $company->upi_id . "&am=" . $invoice->balance_amount) }}" alt="QR" style="width: 55px; height: 55px; border: 1px solid #000; padding: 1px;">
                <div style="font-size: 7.5px;">UPI: {{ $company->upi_id }}</div>
                @endif
            </div>

            <div style="text-align: right; width: 180px;">
                <div style="font-size: 9px; font-weight: 700;">For {{ $company->name }}</div>
                <div style="height: 35px;"></div>
                <div style="border-top: 1px solid #000; padding-top: 2px; font-size: 8.5px; font-weight: 700;">
                    Authorized Signatory
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>