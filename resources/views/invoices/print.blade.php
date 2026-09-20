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
            margin: 8mm 10mm 8mm 10mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
            background: #0b1120;
            -webkit-font-smoothing: antialiased;
        }

        /* Floating Toolbar */
        .toolbar {
            background: #0f172a;
            color: #fff;
            padding: 10px 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0,0,0,0.35);
            font-size: 12px;
            border-bottom: 1px solid #334155;
        }
        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-tab {
            padding: 6px 14px;
            border-radius: 8px;
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
            box-shadow: 0 0 12px rgba(99,102,241,0.4);
        }
        .toolbar-checkbox {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 11px;
            color: #cbd5e1;
            user-select: none;
        }
        .btn-print {
            padding: 8px 20px;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 800;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 10px rgba(16,185,129,0.35);
            transition: background 0.2s;
        }
        .btn-print:hover {
            background: #059669;
        }

        /* Screen Presentation Wrapper */
        .page-viewport {
            padding: 24px 0 40px;
            display: flex;
            justify-content: center;
        }

        /* Full-Page A4 Sheet (Exact 210mm x 297mm Container) */
        .invoice-sheet {
            background: #ffffff;
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            padding: 10mm 12mm 10mm 12mm;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Print Media Overrides */
        @media print {
            body, html {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 190mm !important;
                height: 281mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print, .toolbar {
                display: none !important;
            }
            .page-viewport {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .invoice-sheet {
                width: 100% !important;
                height: 280mm !important;
                max-height: 280mm !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                overflow: hidden !important;
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

<!-- FLOATING TOOLBAR -->
<div class="toolbar no-print">
    <div class="toolbar-group">
        <span style="font-weight: 800; color: #fff;">Template:</span>
        <button type="button" class="btn-tab" :class="template === 'modern' ? 'active' : ''" @click="template = 'modern'">
            ✨ Template 1: Modern Executive
        </button>
        <button type="button" class="btn-tab" :class="template === 'classic' ? 'active' : ''" @click="template = 'classic'">
            📋 Template 2: Classic Corporate (Tally GST)
        </button>
    </div>

    <div class="toolbar-group">
        <span style="font-weight: 800; color: #94a3b8; font-size: 11px;">Visibility:</span>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showBank">
            <span>Bank Wire</span>
        </label>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showQr">
            <span>Payment QR</span>
        </label>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showNotes">
            <span>Terms / Notes</span>
        </label>
    </div>

    <div>
        <button class="btn-print" onclick="window.print()">
            <span>🖨️ Print Full 1-Page A4</span>
        </button>
    </div>
</div>

<div class="page-viewport">
    
    <!-- ======================================================== -->
    <!-- TEMPLATE 1: MODERN EXECUTIVE (FULL 1-PAGE A4)           -->
    <!-- ======================================================== -->
    <div x-show="template === 'modern'" class="invoice-sheet">
        
        <!-- TOP SECTION: HEADER & PARTIES -->
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <!-- Brand Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #4f46e5; padding-bottom: 8px;">
                <div>
                    @if($company->logo_path)
                        <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" style="max-height: 42px; margin-bottom: 4px;">
                    @endif
                    <div style="font-size: 8.5px; font-weight: 800; letter-spacing: 1.5px; color: #4f46e5; text-transform: uppercase;">
                        {{ $invoice->type === 'proforma' ? 'PROFORMA INVOICE / ESTIMATE' : 'TAX INVOICE' }}
                    </div>
                    <h1 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $company->name }}</h1>
                    <p style="color: #475569; font-size: 9.5px; max-width: 360px; line-height: 1.35; margin-top: 2px;">
                        {{ $company->address ?: 'Corporate Office' }}, {{ $company->city }}, {{ $company->state }} {{ $company->pincode ? '- ' . $company->pincode : '' }}
                    </p>
                    <div style="font-size: 9.5px; color: #1e293b; margin-top: 3px; display: flex; gap: 12px;" class="mono">
                        @if($company->gstin)<div>GSTIN: <strong style="color: #0f172a;">{{ $company->gstin }}</strong></div>@endif
                        @if($company->phone)<div>Phone: {{ $company->phone }}</div>@endif
                        @if($company->email)<div>Email: {{ $company->email }}</div>@endif
                    </div>
                </div>

                <div style="text-align: right;">
                    <div class="mono" style="font-size: 18px; font-weight: 800; color: #4f46e5;">#{{ $invoice->invoice_number }}</div>
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 3px;">
                        Date: <strong style="color: #0f172a;">{{ $invoice->invoice_date->format('d M, Y') }}</strong>
                    </div>
                    @if($invoice->due_date)
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 1px;">
                        Due Date: <strong style="color: #0f172a;">{{ $invoice->due_date->format('d M, Y') }}</strong>
                    </div>
                    @endif
                    <div style="margin-top: 6px;">
                        <span style="display: inline-block; padding: 2.5px 10px; border-radius: 6px; font-size: 9px; font-weight: 800; text-transform: uppercase; background: {{ $invoice->status === 'paid' ? '#dcfce7; color: #166534;' : ($invoice->status === 'partially_paid' ? '#e0e7ff; color: #3730a3;' : '#fef3c7; color: #92400e;') }}">
                            ● {{ strtoupper(str_replace('_', ' ', $invoice->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Billed To & Place of Supply Grid -->
            <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                <div style="width: 58%;">
                    <span style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">BILLED TO / RECEIVER:</span>
                    <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $invoice->customer->name ?? 'Walk-in Customer' }}</div>
                    @if($invoice->customer && $invoice->customer->company_name)
                        <div style="font-size: 9.5px; color: #334155; font-weight: 600;">{{ $invoice->customer->company_name }}</div>
                    @endif
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 2px; line-height: 1.3;">
                        {{ $invoice->customer->address ?? '' }} {{ $invoice->customer->city ?? '' }} {{ $invoice->customer->state ?? '' }}
                    </div>
                    @if($invoice->customer && $invoice->customer->gstin)
                        <div class="mono" style="font-size: 9.5px; color: #1e293b; margin-top: 2px;">
                            GSTIN: <strong style="color: #4f46e5;">{{ $invoice->customer->gstin }}</strong>
                        </div>
                    @endif
                </div>

                <div style="width: 38%; text-align: right; font-size: 9.5px;">
                    <div style="color: #64748b; font-weight: 700; font-size: 8.5px; text-transform: uppercase;">PLACE OF SUPPLY:</div>
                    <div style="font-weight: 800; color: #0f172a; margin-top: 2px;">
                        {{ $invoice->sale_type === 'LOCAL' ? 'Intra-State (' . $company->state . ')' : 'Inter-State (Central IGST)' }}
                    </div>
                    <div style="color: #64748b; margin-top: 4px;">
                        Tax Mechanism: <strong style="color: #0f172a;">{{ $invoice->effective_tax_mode === 'detailed' ? 'Detailed Split CGST/SGST' : 'Simple Combined Rate' }}</strong>
                    </div>
                    <div style="color: #64748b; margin-top: 1px;">
                        Reverse Charge: <strong style="color: #0f172a;">No</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- MIDDLE SECTION: ITEMS TABLE (EXPANDED TO FILL FULL A4 PAGE) -->
        <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: flex-start; margin-top: 10px; margin-bottom: 10px;">
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <thead>
                        <tr style="background: #f1f5f9; color: #475569; font-size: 9px; font-weight: 800; text-transform: uppercase; border-bottom: 1.5px solid #cbd5e1;">
                            <th style="padding: 7px 8px; text-align: center; width: 35px;">#</th>
                            <th style="padding: 7px 10px; text-align: left;">Item Description</th>
                            <th style="padding: 7px 8px; text-align: center; width: 65px;">HSN/SAC</th>
                            <th style="padding: 7px 8px; text-align: center; width: 60px;">Qty</th>
                            <th style="padding: 7px 10px; text-align: right; width: 85px;">Rate (₹)</th>
                            <th style="padding: 7px 8px; text-align: center; width: 60px;">Tax %</th>
                            <th style="padding: 7px 10px; text-align: right; width: 95px;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="mono">
                        @foreach($invoice->items as $idx => $item)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 8px; text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                            <td style="padding: 8px 10px; font-family: 'Plus Jakarta Sans', sans-serif;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 10.5px;">{{ $item->description }}</div>
                                @if(!empty($item->domain_name) || !empty($item->service_period_start))
                                <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                                    @if(!empty($item->domain_name))
                                        <span style="font-weight: 700; color: #4f46e5;">🌐 {{ $item->domain_name }}</span>
                                    @endif
                                    @if(!empty($item->service_period_start))
                                        <span style="margin-left: 4px;">Period: {{ \Carbon\Carbon::parse($item->service_period_start)->format('d M Y') }} to {{ \Carbon\Carbon::parse($item->service_period_end)->format('d M Y') }}</span>
                                    @endif
                                    @if(!empty($item->billing_cycle))
                                        <span style="background: #f1f5f9; padding: 1px 4px; border-radius: 3px; color: #334155; margin-left: 3px;">({{ $item->billing_cycle }})</span>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td style="padding: 8px; text-align: center; color: #64748b;">{{ $item->hsn_sac ?: '-' }}</td>
                            <td style="padding: 8px; text-align: center; color: #0f172a; font-weight: 600;">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td style="padding: 8px 10px; text-align: right; color: #0f172a;">{{ number_format($item->rate, 2) }}</td>
                            <td style="padding: 8px; text-align: center; font-weight: 700; color: #4f46e5;">{{ $item->gst_percent }}%</td>
                            <td style="padding: 8px 10px; text-align: right; font-weight: 700; color: #0f172a;">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Amount in Words & Totals Box (Right under items) -->
                <div style="border-top: 1.5px solid #cbd5e1; background: #fafafa;">
                    <div style="display: flex; justify-content: space-between;">
                        <!-- Amount In Words & Terms on Left -->
                        <div style="width: 58%; padding: 10px 12px; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <span style="font-size: 8px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">TOTAL AMOUNT IN WORDS:</span>
                                <div style="font-size: 10px; font-weight: 800; color: #0f172a; margin-top: 2px; line-height: 1.4;">
                                    {{ $invoice->amount_in_words }}
                                </div>
                            </div>
                            @if($invoice->notes)
                            <div style="margin-top: 6px;" x-show="showNotes">
                                <span style="font-size: 8px; font-weight: 700; text-transform: uppercase; color: #64748b;">Notes / Remarks:</span>
                                <div style="font-size: 9px; color: #475569; margin-top: 1px;">{{ $invoice->notes }}</div>
                            </div>
                            @endif
                        </div>

                        <!-- Numerical Totals on Right -->
                        <div style="width: 42%; padding: 8px 12px;" class="mono">
                            <div style="display: flex; justify-content: space-between; font-size: 9.5px; padding: 2px 0;">
                                <span style="color: #64748b;">Taxable Value:</span>
                                <span style="font-weight: 600; color: #0f172a;">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                            </div>

                            @if($invoice->effective_tax_mode === 'simple')
                                <div style="display: flex; justify-content: space-between; font-size: 9.5px; padding: 2px 0; color: #4f46e5; font-weight: 700;">
                                    <span>Total GST:</span>
                                    <span>₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</span>
                                </div>
                            @else
                                @if($invoice->sale_type === 'LOCAL')
                                    <div style="display: flex; justify-content: space-between; font-size: 9px; padding: 1.5px 0; color: #059669;">
                                        <span>CGST:</span>
                                        <span>₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 9px; padding: 1.5px 0; color: #059669;">
                                        <span>SGST:</span>
                                        <span>₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                                    </div>
                                @else
                                    <div style="display: flex; justify-content: space-between; font-size: 9.5px; padding: 2px 0; color: #7c3aed; font-weight: 600;">
                                        <span>IGST (Integrated):</span>
                                        <span>₹{{ number_format($invoice->igst_amount, 2) }}</span>
                                    </div>
                                @endif
                            @endif

                            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 800; color: #0f172a; border-top: 1.5px solid #0f172a; margin-top: 4px; padding-top: 4px;">
                                <span>Grand Total:</span>
                                <span style="color: #4f46e5;">₹{{ number_format($invoice->total_amount, 2) }}</span>
                            </div>

                            @if($invoice->paid_amount > 0)
                            <div style="display: flex; justify-content: space-between; font-size: 9px; color: #166534; font-weight: 600; margin-top: 2px;">
                                <span>Paid to Date:</span>
                                <span>- ₹{{ number_format($invoice->paid_amount, 2) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #b45309; border-top: 1px dashed #cbd5e1; margin-top: 2px; padding-top: 2px;">
                                <span>Balance Due:</span>
                                <span>₹{{ number_format($invoice->balance_amount, 2) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM SECTION: SETTLEMENT & SIGNATURE FOOTER (DOCKED AT BOTTOM) -->
        <div style="border-top: 2px solid #0f172a; padding-top: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 14px;">
                
                <!-- Bank Info -->
                <div style="width: 38%; font-size: 9px; color: #334155; line-height: 1.45;" x-show="showBank">
                    <div style="font-weight: 800; color: #0f172a; font-size: 9.5px; text-transform: uppercase; margin-bottom: 2px; letter-spacing: 0.5px;">
                        🏦 Bank Wire Remittance:
                    </div>
                    <div class="mono" style="background: #f8fafc; padding: 6px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        @if($company->bank_name)<div>Bank: <strong style="color: #0f172a;">{{ $company->bank_name }}</strong></div>@endif
                        @if($company->bank_account_number)<div>A/C No: <strong style="color: #0f172a;">{{ $company->bank_account_number }}</strong></div>@endif
                        @if($company->bank_ifsc)<div>IFSC Code: <strong style="color: #4f46e5;">{{ $company->bank_ifsc }}</strong></div>@endif
                        @if($company->bank_branch)<div>Branch: {{ $company->bank_branch }}</div>@endif
                    </div>
                </div>

                <!-- QR Code Block -->
                <div style="width: 24%; text-align: center;" x-show="showQr && '{{ $company->upi_id }}' !== ''">
                    @if($company->upi_id)
                    @php
                        $upiPayload = "upi://pay?pa=" . urlencode($company->upi_id) . "&pn=" . urlencode($company->name) . "&am=" . $invoice->balance_amount . "&cu=INR&tn=" . urlencode("Invoice " . $invoice->invoice_number);
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=85x85&data=" . urlencode($upiPayload);
                    @endphp
                    <div style="display: inline-block; background: #fff; padding: 4px; border: 1px solid #cbd5e1; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <img src="{{ $qrUrl }}" alt="UPI QR" style="width: 65px; height: 65px; display: block; margin: 0 auto;">
                        <div style="font-size: 7.5px; color: #0f172a; margin-top: 2px; font-weight: 700; font-family: 'JetBrains Mono', monospace;">
                            Scan to Pay UPI
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Terms & Authorized Signatory -->
                <div style="width: 38%; text-align: right; display: flex; flex-direction: column; justify-content: space-between; min-height: 75px;">
                    <div style="font-size: 8px; color: #64748b; line-height: 1.3;" x-show="showNotes">
                        1. Payment due within specified due date.<br>
                        2. Certified that particulars given above are true and correct.
                    </div>
                    <div>
                        <div style="font-size: 9px; font-weight: 700; color: #0f172a; text-transform: uppercase;">
                            For {{ $company->name }}
                        </div>
                        <div style="height: 38px;"></div>
                        <div style="border-top: 1px solid #94a3b8; padding-top: 2px; font-weight: 800; font-size: 9px; color: #0f172a;">
                            Authorized Signatory
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TEMPLATE 2: CLASSIC CORPORATE (TALLY GST FULL-BORDERED) -->
    <!-- ======================================================== -->
    <div x-show="template === 'classic'" x-cloak class="invoice-sheet" style="padding: 0 !important; border: 1.5px solid #000;">
        <div style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
            
            <!-- SECTION 1: HEADER & PARTIES (BORDERED ACCOUNTING FORMAT) -->
            <div>
                <!-- Main Header Title -->
                <div style="text-align: center; border-bottom: 1.5px solid #000; padding: 6px 10px; background: #fafafa;">
                    <div style="font-size: 11px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase;">
                        {{ $invoice->type === 'proforma' ? 'PROFORMA INVOICE / ESTIMATE' : 'TAX INVOICE' }}
                    </div>
                    <h1 style="font-size: 19px; font-weight: 900; color: #000; text-transform: uppercase; margin-top: 2px;">
                        {{ $company->name }}
                    </h1>
                    <p style="font-size: 9px; color: #1e293b; margin-top: 1px;">
                        {{ $company->address }}, {{ $company->city }}, {{ $company->state }} {{ $company->pincode ? '- ' . $company->pincode : '' }}
                    </p>
                    <div style="font-size: 9px; color: #000; margin-top: 2px;" class="mono">
                        @if($company->gstin)<span>GSTIN: <strong>{{ $company->gstin }}</strong></span>@endif
                        @if($company->phone)<span style="margin-left: 10px;">Phone: <strong>{{ $company->phone }}</strong></span>@endif
                        @if($company->email)<span style="margin-left: 10px;">Email: <strong>{{ $company->email }}</strong></span>@endif
                    </div>
                </div>

                <!-- 2-Column Meta & Parties Grid -->
                <table style="width: 100%; border-bottom: 1.5px solid #000; font-size: 9.5px;">
                    <tr>
                        <!-- Left: Receiver / Billed To -->
                        <td style="width: 50%; vertical-align: top; border-right: 1.5px solid #000; padding: 6px 10px;">
                            <div style="font-weight: 800; text-transform: uppercase; font-size: 8.5px; color: #475569; letter-spacing: 0.5px;">
                                Details of Receiver (Billed To):
                            </div>
                            <div style="font-size: 12px; font-weight: 800; color: #000; margin-top: 2px;">
                                {{ $invoice->customer->name ?? 'Walk-in Customer' }}
                            </div>
                            @if($invoice->customer && $invoice->customer->company_name)
                                <div style="font-weight: 700; color: #1e293b; font-size: 10px;">{{ $invoice->customer->company_name }}</div>
                            @endif
                            <div style="color: #334155; margin-top: 2px; line-height: 1.35;">
                                {{ $invoice->customer->address ?? '' }} {{ $invoice->customer->city ?? '' }} {{ $invoice->customer->state ?? '' }}
                            </div>
                            @if($invoice->customer && $invoice->customer->gstin)
                                <div class="mono" style="margin-top: 3px; font-size: 9.5px;">
                                    GSTIN / UIN: <strong style="color: #000;">{{ $invoice->customer->gstin }}</strong>
                                </div>
                            @endif
                            @if($invoice->customer && $invoice->customer->phone)
                                <div class="mono" style="font-size: 9px; color: #475569;">
                                    Phone: {{ $invoice->customer->phone }}
                                </div>
                            @endif
                        </td>

                        <!-- Right: Invoice Metadata -->
                        <td style="width: 50%; vertical-align: top; padding: 6px 10px;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding-bottom: 2px;">
                                <span style="font-weight: 600;">Invoice No:</span>
                                <strong class="mono" style="font-size: 12px; color: #000;">{{ $invoice->invoice_number }}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding: 2px 0;">
                                <span style="font-weight: 600;">Invoice Date:</span>
                                <strong>{{ $invoice->invoice_date->format('d/m/Y') }}</strong>
                            </div>
                            @if($invoice->due_date)
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding: 2px 0;">
                                <span style="font-weight: 600;">Payment Due Date:</span>
                                <strong>{{ $invoice->due_date->format('d/m/Y') }}</strong>
                            </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding: 2px 0;">
                                <span style="font-weight: 600;">Place of Supply:</span>
                                <strong>{{ $invoice->sale_type === 'LOCAL' ? $company->state . ' (Intrastate)' : 'Interstate (Central IGST)' }}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 2px;">
                                <span style="font-weight: 600;">Reverse Charge:</span>
                                <strong>No</strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- SECTION 2: ITEMS TABLE (EXPANDED TO FULL HEIGHT) -->
            <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <table style="width: 100%; border-collapse: collapse; font-size: 9.5px;">
                    <thead>
                        <tr style="background: #e2e8f0; border-bottom: 1.5px solid #000; font-weight: 900; text-transform: uppercase;">
                            <th style="width: 30px; text-align: center; border-right: 1px solid #000; padding: 5px;">#</th>
                            <th style="text-align: left; border-right: 1px solid #000; padding: 5px 8px;">Description of Goods / Services</th>
                            <th style="width: 60px; text-align: center; border-right: 1px solid #000; padding: 5px;">HSN/SAC</th>
                            <th style="width: 45px; text-align: center; border-right: 1px solid #000; padding: 5px;">Qty</th>
                            <th style="width: 75px; text-align: right; border-right: 1px solid #000; padding: 5px;">Rate (₹)</th>
                            <th style="width: 50px; text-align: center; border-right: 1px solid #000; padding: 5px;">Tax %</th>
                            <th style="width: 85px; text-align: right; padding: 5px 8px;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="mono">
                        @foreach($invoice->items as $idx => $item)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="text-align: center; border-right: 1px solid #000; padding: 7px 4px; vertical-align: top;">{{ $idx + 1 }}</td>
                            <td style="border-right: 1px solid #000; padding: 7px 8px; font-family: 'Plus Jakarta Sans', sans-serif; vertical-align: top;">
                                <strong style="color: #000; font-size: 10px;">{{ $item->description }}</strong>
                                @if(!empty($item->domain_name) || !empty($item->service_period_start))
                                    <div style="font-size: 8px; color: #475569; margin-top: 1px;">
                                        @if($item->domain_name)Asset: {{ $item->domain_name }} @endif
                                        @if($item->service_period_start)| Period: {{ \Carbon\Carbon::parse($item->service_period_start)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($item->service_period_end)->format('d/m/Y') }} @endif
                                        @if($item->billing_cycle)({{ $item->billing_cycle }})@endif
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; border-right: 1px solid #000; padding: 7px 4px; vertical-align: top;">{{ $item->hsn_sac ?: '-' }}</td>
                            <td style="text-align: center; border-right: 1px solid #000; padding: 7px 4px; vertical-align: top;">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td style="text-align: right; border-right: 1px solid #000; padding: 7px 6px; vertical-align: top;">{{ number_format($item->rate, 2) }}</td>
                            <td style="text-align: center; border-right: 1px solid #000; padding: 7px 4px; vertical-align: top; font-weight: 700;">{{ $item->gst_percent }}%</td>
                            <td style="text-align: right; padding: 7px 8px; font-weight: 700; color: #000; vertical-align: top;">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Summary & Calculations Bar -->
                <div>
                    <!-- Bold Total Calculation Row -->
                    <table style="width: 100%; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; font-size: 9.5px;">
                        <tr style="background: #f8fafc;">
                            <td style="padding: 5px 8px; border-right: 1.5px solid #000; width: 55%; vertical-align: top;">
                                <div style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: #475569;">
                                    Total Amount in Words:
                                </div>
                                <div style="font-weight: 800; color: #000; font-size: 10px; margin-top: 2px; line-height: 1.35;">
                                    {{ $invoice->amount_in_words }}
                                </div>
                            </td>
                            <td style="padding: 0; width: 45%; vertical-align: top;">
                                <table class="mono" style="width: 100%; font-size: 9.5px;">
                                    <tr style="border-bottom: 1px dotted #ccc;">
                                        <td style="padding: 3px 8px;">Taxable Value:</td>
                                        <td style="text-align: right; padding: 3px 8px; font-weight: 700;">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
                                    </tr>
                                    @if($invoice->effective_tax_mode === 'simple')
                                        <tr style="border-bottom: 1px dotted #ccc;">
                                            <td style="padding: 3px 8px;">GST Total:</td>
                                            <td style="text-align: right; padding: 3px 8px; font-weight: 700;">₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</td>
                                        </tr>
                                    @else
                                        @if($invoice->sale_type === 'LOCAL')
                                            <tr style="border-bottom: 1px dotted #ccc;">
                                                <td style="padding: 2px 8px;">CGST:</td>
                                                <td style="text-align: right; padding: 2px 8px;">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                                            </tr>
                                            <tr style="border-bottom: 1px dotted #ccc;">
                                                <td style="padding: 2px 8px;">SGST:</td>
                                                <td style="text-align: right; padding: 2px 8px;">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                                            </tr>
                                        @else
                                            <tr style="border-bottom: 1px dotted #ccc;">
                                                <td style="padding: 3px 8px;">IGST (Integrated):</td>
                                                <td style="text-align: right; padding: 3px 8px;">₹{{ number_format($invoice->igst_amount, 2) }}</td>
                                            </tr>
                                        @endif
                                    @endif
                                    <tr style="border-top: 1.5px solid #000; font-size: 11.5px; font-weight: 900; background: #f1f5f9;">
                                        <td style="padding: 5px 8px;">Grand Total:</td>
                                        <td style="text-align: right; padding: 5px 8px; color: #000;">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- SECTION 3: SETTLEMENT & SIGNATORY FOOTER (BORDERED BOXES) -->
            <div style="border-top: 1.5px solid #000;">
                <table style="width: 100%; font-size: 9px;">
                    <tr>
                        <!-- Bank Details -->
                        <td style="width: 35%; vertical-align: top; border-right: 1.5px solid #000; padding: 6px 8px;" x-show="showBank">
                            <strong style="text-transform: uppercase; font-size: 8.5px; display: block; border-bottom: 1px solid #ccc; padding-bottom: 2px; color: #000;">
                                Bank Wire Remittance:
                            </strong>
                            <div class="mono" style="margin-top: 4px; line-height: 1.45;">
                                @if($company->bank_name)<div>Bank: <strong>{{ $company->bank_name }}</strong></div>@endif
                                @if($company->bank_account_number)<div>A/C: <strong>{{ $company->bank_account_number }}</strong></div>@endif
                                @if($company->bank_ifsc)<div>IFSC: <strong>{{ $company->bank_ifsc }}</strong></div>@endif
                                @if($company->bank_branch)<div>Branch: {{ $company->bank_branch }}</div>@endif
                            </div>
                        </td>

                        <!-- QR Code -->
                        <td style="width: 20%; vertical-align: middle; text-align: center; border-right: 1.5px solid #000; padding: 6px;" x-show="showQr && '{{ $company->upi_id }}' !== ''">
                            @if($company->upi_id)
                            @php
                                $upiPayload = "upi://pay?pa=" . urlencode($company->upi_id) . "&pn=" . urlencode($company->name) . "&am=" . $invoice->balance_amount . "&cu=INR&tn=" . urlencode("Invoice " . $invoice->invoice_number);
                                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=85x85&data=" . urlencode($upiPayload);
                            @endphp
                            <img src="{{ $qrUrl }}" alt="UPI QR" style="width: 65px; height: 65px; display: inline-block;">
                            <div style="font-size: 7.5px; font-weight: 700; font-family: 'JetBrains Mono', monospace; margin-top: 2px;">
                                Scan to Pay UPI
                            </div>
                            @endif
                        </td>

                        <!-- Terms & Conditions -->
                        <td style="width: 25%; vertical-align: top; border-right: 1.5px solid #000; padding: 6px 8px;" x-show="showNotes">
                            <strong style="text-transform: uppercase; font-size: 8px; display: block; color: #475569;">
                                Terms & Conditions:
                            </strong>
                            <div style="font-size: 7.5px; color: #334155; line-height: 1.35; margin-top: 2px;">
                                1. Payment due within specified due date.<br>
                                2. Goods once sold will not be returned.<br>
                                3. Certified that particulars given are true.
                            </div>
                            @if($invoice->notes)
                            <div style="margin-top: 4px; font-size: 7.5px;">
                                <strong>Notes:</strong> {{ $invoice->notes }}
                            </div>
                            @endif
                        </td>

                        <!-- Authorized Signatory -->
                        <td style="width: 20%; vertical-align: top; text-align: center; padding: 6px 8px; display: flex; flex-direction: column; justify-content: space-between; min-height: 70px;">
                            <div style="font-size: 8px; font-weight: 700; color: #000; text-transform: uppercase;">
                                For {{ $company->name }}
                            </div>
                            <div style="height: 35px;"></div>
                            <div style="border-top: 1px solid #000; padding-top: 2px; font-weight: 900; font-size: 8.5px; color: #000;">
                                Authorized Signatory
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

</div>

</body>
</html>
