<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice_{{ $invoice->invoice_number }} - {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            gap: 8px;
        }
        .btn-tab {
            padding: 6px 12px;
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
            background: #059669;
            color: #fff;
            border-color: #10b981;
            box-shadow: 0 0 10px rgba(16,185,129,0.4);
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
            padding: 8px 18px;
            background: #059669;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 800;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 10px rgba(5,150,105,0.35);
            transition: background 0.2s;
        }
        .btn-print:hover {
            background: #047857;
        }

        .page-viewport {
            padding: 24px 0 40px;
            display: flex;
            justify-content: center;
        }

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
@php $customer = $customer ?? $invoice->customer; @endphp
<body x-data="{ 
    template: '{{ request('template', $company->invoice_design_template ?? 'modern') }}',
    showBank: {{ ($company->show_bank_on_invoice ?? true) ? 'true' : 'false' }},
    showQr: {{ (($company->show_qr_on_invoice ?? true) && $company->enable_upi_qr && !empty($company->upi_id)) ? 'true' : 'false' }},
    showNotes: true
}">

<!-- FLOATING TOOLBAR WITH 3 FORMATS -->
<div class="toolbar no-print">
    <div class="toolbar-group">
        <span style="font-weight: 800; color: #fff;">Format:</span>
        <button type="button" class="btn-tab" :class="template === 'modern' ? 'active' : ''" @click="template = 'modern'">
            ✨ Template 1: Modern Executive
        </button>
        <button type="button" class="btn-tab" :class="template === 'classic' ? 'active' : ''" @click="template = 'classic'">
            📋 Template 2: Classic Corporate
        </button>
        <button type="button" class="btn-tab" :class="template === 'greenstudio' ? 'active' : ''" @click="template = 'greenstudio'">
            🌿 Template 3: Green Studio (Digital & Hosting)
        </button>
    </div>

    <div class="toolbar-group">
        <span style="font-weight: 800; color: #94a3b8; font-size: 11px;">Visibility:</span>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showBank">
            <span>Bank Details</span>
        </label>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showQr">
            <span>Payment QR</span>
        </label>
        <label class="toolbar-checkbox">
            <input type="checkbox" x-model="showNotes">
            <span>Terms & Notes</span>
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
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Brand Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #0f172a; padding-bottom: 8px;">
                <div>
                    @if($company->logo_path)
                        <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" style="max-height: 40px; margin-bottom: 4px;">
                    @endif
                    <div style="font-size: 8.5px; font-weight: 800; letter-spacing: 1.5px; color: #475569; text-transform: uppercase;">
                        {{ $invoice->type === 'proforma' ? 'PROFORMA INVOICE / ESTIMATE' : 'TAX INVOICE' }}
                    </div>
                    <h1 style="font-size: 19px; font-weight: 900; color: #0f172a; margin-top: 2px;">{{ $company->name }}</h1>
                    <p style="color: #475569; font-size: 9.5px; max-width: 360px; line-height: 1.35; margin-top: 2px;">
                        {{ $company->address ?: 'Corporate Office' }}, {{ $company->city }}, {{ $company->state }} {{ $company->pincode ? '- ' . $company->pincode : '' }}
                    </p>
                    <div style="font-size: 9.5px; color: #1e293b; margin-top: 3px; display: flex; gap: 12px;" class="mono">
                        @if($company->gstin)<div>GSTIN: <strong style="color: #0f172a;">{{ $company->gstin }}</strong></div>@endif
                        @if($company->phone)<div>Phone: {{ $company->phone }}</div>@endif
                    </div>
                </div>

                <div style="text-align: right;">
                    <div class="mono" style="font-size: 18px; font-weight: 900; color: #0f172a;">#{{ $invoice->invoice_number }}</div>
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 3px;">
                        Date: <strong style="color: #0f172a;">{{ $invoice->invoice_date->format('d M, Y') }}</strong>
                    </div>
                    @if($invoice->due_date)
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 1px;">
                        Due Date: <strong style="color: #0f172a;">{{ $invoice->due_date->format('d M, Y') }}</strong>
                    </div>
                    @endif
                    <div style="margin-top: 6px;">
                        <span style="display: inline-block; padding: 2.5px 10px; border-radius: 6px; font-size: 9px; font-weight: 800; text-transform: uppercase; background: {{ $invoice->status === 'paid' ? '#dcfce7; color: #166534;' : ($invoice->status === 'partially_paid' ? '#fef3c7; color: #92400e;' : '#fee2e2; color: #991b1b;') }}">
                            ● {{ strtoupper(str_replace('_', ' ', $invoice->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Billed To & Place of Supply Grid -->
            <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                <div style="width: 58%;">
                    <span style="font-size: 8px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">BILLED TO / RECEIVER:</span>
                    <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $invoice->customer->name ?? 'Walk-in Customer' }}</div>
                    @if($invoice->customer && $invoice->customer->company_name)
                        <div style="font-size: 9.5px; color: #334155; font-weight: 600;">{{ $invoice->customer->company_name }}</div>
                    @endif
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 2px; line-height: 1.3;">
                        {{ $invoice->customer->address ?? '' }} {{ $invoice->customer->city ?? '' }} {{ $invoice->customer->state ?? '' }}
                    </div>
                    @if($invoice->customer && $invoice->customer->gstin)
                        <div class="mono" style="font-size: 9.5px; color: #1e293b; margin-top: 2px;">
                            GSTIN: <strong style="color: #0f172a;">{{ $invoice->customer->gstin }}</strong>
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
                </div>
            </div>
        </div>

        <!-- MIDDLE SECTION: ITEMS TABLE (EXPANDED TO FILL FULL A4 PAGE) -->
        <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: flex-start; margin-top: 8px; margin-bottom: 8px;">
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <thead>
                        <tr style="background: #f1f5f9; color: #334155; font-size: 9px; font-weight: 800; text-transform: uppercase; border-bottom: 1.5px solid #cbd5e1;">
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
                                        <span style="font-weight: 700; color: #059669;">🌐 {{ $item->domain_name }}</span>
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
                            <td style="padding: 8px; text-align: center; font-weight: 700; color: #059669;">{{ $item->gst_percent }}%</td>
                            <td style="padding: 8px 10px; text-align: right; font-weight: 700; color: #0f172a;">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Amount in Words & Totals Box -->
                <div style="border-top: 1.5px solid #cbd5e1; background: #fafafa;">
                    <div style="display: flex; justify-content: space-between;">
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

                        <div style="width: 42%; padding: 8px 12px;" class="mono">
                            <div style="display: flex; justify-content: space-between; font-size: 9.5px; padding: 2px 0;">
                                <span style="color: #64748b;">Taxable Value:</span>
                                <span style="font-weight: 600; color: #0f172a;">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                            </div>

                            @if($invoice->effective_tax_mode === 'simple')
                                <div style="display: flex; justify-content: space-between; font-size: 9.5px; padding: 2px 0; color: #059669; font-weight: 700;">
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

                            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 900; color: #0f172a; border-top: 1.5px solid #0f172a; margin-top: 4px; padding-top: 4px;">
                                <span>Grand Total:</span>
                                <span>₹{{ number_format($invoice->total_amount, 2) }}</span>
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

        <!-- BOTTOM SECTION: SETTLEMENT & SIGNATURE FOOTER -->
        <div style="border-top: 2px solid #0f172a; padding-top: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 14px;">
                
                <div style="width: 38%; font-size: 9px; color: #334155; line-height: 1.45;" x-show="showBank">
                    <div style="font-weight: 800; color: #0f172a; font-size: 9.5px; text-transform: uppercase; margin-bottom: 2px; letter-spacing: 0.5px;">
                        🏦 Bank Wire Remittance:
                    </div>
                    <div class="mono" style="background: #f8fafc; padding: 6px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        @if($company->bank_name)<div>Bank: <strong style="color: #0f172a;">{{ $company->bank_name }}</strong></div>@endif
                        @if($company->bank_account_number)<div>A/C No: <strong style="color: #0f172a;">{{ $company->bank_account_number }}</strong></div>@endif
                        @if($company->bank_ifsc)<div>IFSC Code: <strong style="color: #059669;">{{ $company->bank_ifsc }}</strong></div>@endif
                        @if($company->bank_branch)<div>Branch: {{ $company->bank_branch }}</div>@endif
                    </div>
                </div>

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

                <div style="width: 38%; text-align: right; display: flex; flex-direction: column; justify-content: space-between; min-height: 75px;">
                    <div style="font-size: 8px; color: #64748b; line-height: 1.3;" x-show="showNotes">
                        1. Payment due within specified due date.<br>
                        2. Certified that particulars given above are true and correct.
                    </div>
                    <div>
                        <div style="font-size: 9px; font-weight: 700; color: #0f172a; text-transform: uppercase;">
                            For {{ $company->name }}
                        </div>
                        <div style="height: 35px;"></div>
                        <div style="border-top: 1px solid #94a3b8; padding-top: 2px; font-weight: 800; font-size: 9px; color: #0f172a;">
                            Authorized Signatory
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>


    <!-- ======================================================== -->
    <!-- TEMPLATE 2: CLASSIC CORPORATE (TALLY GST BOXED A4)       -->
    <!-- ======================================================== -->
    <div x-show="template === 'classic'" class="invoice-sheet" style="font-family: 'Inter', -apple-system, sans-serif; font-size: 9.5px; border: 2px solid #0f172a; padding: 0;">
        
        <!-- Header Banner -->
        <div style="border-bottom: 2px solid #0f172a; text-align: center; padding: 6px; background: #f8fafc;">
            <div style="font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #0f172a;">
                TAX INVOICE
            </div>
            <div style="font-size: 8px; color: #475569; font-weight: 600;">
                (Issued under Section 31 of Central Goods and Services Tax Act, 2017)
            </div>
        </div>

        <!-- Supplier & Buyer Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 2px solid #0f172a;">
            <!-- Supplier -->
            <div style="border-right: 1px solid #0f172a; padding: 8px 12px; line-height: 1.45;">
                <div style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase;">Details of Supplier / Consignor:</div>
                <div style="font-size: 13px; font-weight: 900; color: #0f172a; margin: 2px 0;">{{ $company->name }}</div>
                <div style="color: #334155;">{{ $company->address }}, {{ $company->city }}, {{ $company->state }} - {{ $company->pincode }}</div>
                <div style="margin-top: 4px; font-weight: 700; color: #0f172a;">
                    GSTIN: <span class="mono" style="font-weight: 900;">{{ $company->gstin ?? 'URP / UNREGISTERED' }}</span> | State: {{ $company->state }} ({{ $company->state_code ?? 'NA' }})
                </div>
                @if($company->email || $company->phone)
                <div style="font-size: 8.5px; color: #475569;">
                    @if($company->email) Email: {{ $company->email }} @endif
                    @if($company->phone) | Tel: {{ $company->phone }} @endif
                </div>
                @endif
            </div>

            <!-- Invoice Meta Grid -->
            <div style="display: grid; grid-template-rows: repeat(4, auto); font-size: 9px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #cbd5e1; padding: 4px 8px;">
                    <div><span style="color: #64748b;">Invoice No:</span> <strong class="mono" style="color: #0f172a;">{{ $invoice->invoice_number }}</strong></div>
                    <div><span style="color: #64748b;">Dated:</span> <strong class="mono">{{ $invoice->invoice_date->format('d-M-Y') }}</strong></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #cbd5e1; padding: 4px 8px;">
                    <div><span style="color: #64748b;">Due Date:</span> <strong class="mono">{{ $invoice->due_date ? $invoice->due_date->format('d-M-Y') : 'On Receipt' }}</strong></div>
                    <div><span style="color: #64748b;">Payment Status:</span> <strong style="color: {{ $invoice->status === 'paid' ? '#059669' : ($invoice->status === 'cancelled' ? '#dc2626' : '#d97706') }}; text-transform: uppercase;">{{ $invoice->status }}</strong></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #cbd5e1; padding: 4px 8px;">
                    <div><span style="color: #64748b;">Place of Supply:</span> <strong>{{ $invoice->place_of_supply ?? ($customer->state ?? $company->state) }}</strong></div>
                    <div><span style="color: #64748b;">Tax Mechanism:</span> <strong>{{ $invoice->reverse_charge ? 'Reverse Charge (RCM)' : 'Forward Regular' }}</strong></div>
                </div>
                <div style="padding: 4px 8px;">
                    <span style="color: #64748b;">Terms of Delivery / Notes:</span> <span>{{ $invoice->notes ?: 'Subject to local jurisdiction.' }}</span>
                </div>
            </div>
        </div>

        <!-- Buyer Details -->
        <div style="border-bottom: 2px solid #0f172a; padding: 8px 12px; background: #fdfdfd; line-height: 1.45;">
            <div style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase;">Billed To / Buyer (Recipient):</div>
            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div style="font-size: 12px; font-weight: 800; color: #0f172a;">{{ $customer->name }}</div>
                <div style="font-size: 9px; font-weight: 700; color: #0f172a;">
                    GSTIN: <span class="mono" style="font-weight: 800;">{{ $customer->gstin ?? 'URP / Consumer' }}</span>
                </div>
            </div>
            <div style="color: #334155;">{{ $customer->billing_address ?? $customer->address }}, {{ $customer->city }}, {{ $customer->state }} - {{ $customer->pincode }}</div>
            <div style="font-size: 8.5px; color: #475569;">
                State: {{ $customer->state }} ({{ $customer->state_code ?? 'NA' }}) 
                @if($customer->phone) | Phone: {{ $customer->phone }} @endif
                @if($customer->email) | Email: {{ $customer->email }} @endif
            </div>
        </div>

        <!-- Classic Items Table -->
        <table style="width: 100%; border-collapse: collapse; font-size: 9px; border-bottom: 2px solid #0f172a;">
            <thead>
                <tr style="background: #f1f5f9; border-bottom: 1.5px solid #0f172a; text-align: left;">
                    <th style="padding: 5px 6px; width: 28px; text-align: center; border-right: 1px solid #cbd5e1;">Sl.</th>
                    <th style="padding: 5px 8px; border-right: 1px solid #cbd5e1;">Description of Goods / Services</th>
                    <th style="padding: 5px 6px; width: 68px; text-align: center; border-right: 1px solid #cbd5e1;">HSN/SAC</th>
                    <th style="padding: 5px 6px; width: 50px; text-align: right; border-right: 1px solid #cbd5e1;">Qty</th>
                    <th style="padding: 5px 6px; width: 68px; text-align: right; border-right: 1px solid #cbd5e1;">Rate (₹)</th>
                    <th style="padding: 5px 6px; width: 52px; text-align: center; border-right: 1px solid #cbd5e1;">GST %</th>
                    <th style="padding: 5px 8px; width: 85px; text-align: right;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                    <td style="padding: 5px 6px; text-align: center; border-right: 1px solid #e2e8f0;" class="mono">{{ $idx + 1 }}</td>
                    <td style="padding: 5px 8px; border-right: 1px solid #e2e8f0;">
                        <strong style="color: #0f172a;">{{ $item->description }}</strong>
                        @if($item->domain_name || $item->service_period_start)
                        <div style="font-size: 8px; color: #0284c7; margin-top: 1px;">
                            @if($item->domain_name) Domain: {{ $item->domain_name }} @endif
                            @if($item->service_period_start) | Period: {{ $item->service_period_start->format('d/m/Y') }} to {{ $item->service_period_end ? $item->service_period_end->format('d/m/Y') : '' }} @endif
                        </div>
                        @endif
                    </td>
                    <td style="padding: 5px 6px; text-align: center; border-right: 1px solid #e2e8f0;" class="mono">{{ $item->hsn_sac ?: '-' }}</td>
                    <td style="padding: 5px 6px; text-align: right; border-right: 1px solid #e2e8f0;" class="mono">{{ (float)$item->quantity }} {{ $item->unit }}</td>
                    <td style="padding: 5px 6px; text-align: right; border-right: 1px solid #e2e8f0;" class="mono">{{ number_format($item->rate, 2) }}</td>
                    <td style="padding: 5px 6px; text-align: center; border-right: 1px solid #e2e8f0;" class="mono">{{ (float)$item->gst_percent }}%</td>
                    <td style="padding: 5px 8px; text-align: right;" class="mono font-bold">{{ number_format($item->taxable_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 800; border-top: 1.5px solid #0f172a;">
                    <td colspan="3" style="padding: 5px 8px; text-align: right; border-right: 1px solid #cbd5e1;">Total Qty:</td>
                    <td style="padding: 5px 6px; text-align: right; border-right: 1px solid #cbd5e1;" class="mono">{{ (float)$invoice->items->sum('quantity') }}</td>
                    <td colspan="2" style="padding: 5px 8px; text-align: right; border-right: 1px solid #cbd5e1;">Taxable Value:</td>
                    <td style="padding: 5px 8px; text-align: right;" class="mono">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Classic Bottom Summary & Bank Grid -->
        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; border-bottom: 2px solid #0f172a;">
            <!-- Left: Amount in Words & Bank Details -->
            <div style="border-right: 1px solid #0f172a; padding: 8px 12px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase;">Amount Chargeable (in words):</div>
                    <div style="font-size: 9.5px; font-weight: 800; color: #0f172a; margin: 3px 0; font-style: italic;">
                        INR {{ ucwords(\NumberFormatter::create("en_IN", \NumberFormatter::SPELLOUT)->format($invoice->total_amount)) }} Only
                    </div>
                </div>

                <div style="margin-top: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px;" x-show="showBank">
                    <div style="font-weight: 800; font-size: 8.5px; text-transform: uppercase; color: #0f172a; margin-bottom: 2px;">
                        Bank Wire Remittance:
                    </div>
                    <div class="mono" style="font-size: 8.5px; line-height: 1.4; color: #334155;">
                        @if($company->bank_name)<div>Bank: <strong style="color: #0f172a;">{{ $company->bank_name }}</strong></div>@endif
                        @if($company->bank_account_number)<div>A/C No: <strong style="color: #0f172a;">{{ $company->bank_account_number }}</strong></div>@endif
                        @if($company->bank_ifsc)<div>IFSC Code: <strong style="color: #059669;">{{ $company->bank_ifsc }}</strong></div>@endif
                        @if($company->bank_branch)<div>Branch: {{ $company->bank_branch }}</div>@endif
                    </div>
                </div>
            </div>

            <!-- Right: Calculation Rows -->
            <div style="padding: 6px 10px; font-size: 9px; line-height: 1.6;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding: 2px 0;">
                    <span style="color: #64748b;">Taxable Amount:</span>
                    <strong class="mono">₹{{ number_format($invoice->taxable_amount, 2) }}</strong>
                </div>

                @if($invoice->tax_mode === 'split_cgst_sgst')
                    @if($invoice->cgst_amount > 0)
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding: 2px 0;">
                        <span style="color: #64748b;">Central Tax (CGST):</span>
                        <strong class="mono" style="color: #059669;">+ ₹{{ number_format($invoice->cgst_amount, 2) }}</strong>
                    </div>
                    @endif
                    @if($invoice->sgst_amount > 0)
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding: 2px 0;">
                        <span style="color: #64748b;">State Tax (SGST):</span>
                        <strong class="mono" style="color: #059669;">+ ₹{{ number_format($invoice->sgst_amount, 2) }}</strong>
                    </div>
                    @endif
                @else
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding: 2px 0;">
                        <span style="color: #64748b;">Integrated GST (18% / Standard):</span>
                        <strong class="mono" style="color: #059669;">+ ₹{{ number_format($invoice->gst_amount, 2) }}</strong>
                    </div>
                @endif

                @if($invoice->cess_amount > 0)
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding: 2px 0;">
                    <span style="color: #64748b;">GST Cess:</span>
                    <strong class="mono">+ ₹{{ number_format($invoice->cess_amount, 2) }}</strong>
                </div>
                @endif

                @if($invoice->round_off != 0)
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding: 2px 0;">
                    <span style="color: #64748b;">Round Off:</span>
                    <strong class="mono">{{ $invoice->round_off > 0 ? '+' : '' }}₹{{ number_format($invoice->round_off, 2) }}</strong>
                </div>
                @endif

                <div style="display: flex; justify-content: space-between; border-top: 1.5px solid #0f172a; border-bottom: 1.5px solid #0f172a; padding: 4px 0; margin-top: 4px; font-size: 11px;">
                    <strong style="color: #0f172a; text-transform: uppercase;">Total Invoice Value:</strong>
                    <strong class="mono" style="color: #0f172a; font-size: 13px;">₹{{ number_format($invoice->total_amount, 2) }}</strong>
                </div>
            </div>
        </div>

        <!-- Classic Signatory Footer -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; padding: 8px 12px; align-items: flex-end;">
            <div style="font-size: 8px; color: #64748b; line-height: 1.3;" x-show="showNotes">
                <strong>Declaration:</strong> We declare that this invoice shows the actual price of the goods or services described and that all particulars are true and correct.
            </div>
            <div style="text-align: right;">
                <div style="font-size: 9px; font-weight: 700; color: #0f172a; text-transform: uppercase;">
                    For {{ $company->name }}
                </div>
                <div style="height: 38px;"></div>
                <div style="font-weight: 800; font-size: 9px; color: #0f172a; border-top: 1px solid #64748b; display: inline-block; padding-top: 2px; min-width: 140px;">
                    Authorized Signatory
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TEMPLATE 3: GREEN STUDIO (DIGITAL & HOSTING FORMAT)       -->
    <!-- Matches greenstudio.jixsite.com specification            -->
    <!-- ======================================================== -->
    <div x-show="template === 'greenstudio'" class="invoice-sheet" style="font-family: 'Plus Jakarta Sans', sans-serif; position: relative; overflow: hidden; padding: 32px 36px;">
        
        <!-- Diagonal PAID Corner Ribbon -->
        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; overflow: hidden; pointer-events: none;">
            <div style="position: absolute; transform: rotate(45deg); background: {{ $invoice->status === 'paid' ? '#16a34a' : ($invoice->status === 'cancelled' ? '#dc2626' : '#d97706') }}; color: #ffffff; font-weight: 900; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; text-align: center; width: 160px; top: 26px; right: -38px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); padding: 4px 0;">
                {{ strtoupper($invoice->status) }}
            </div>
        </div>

        <!-- Top Brand Header -->
        <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 22px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #16a34a; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.25);">
                🌿
            </div>
            <div>
                <div style="font-size: 22px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: -0.5px; line-height: 1.1;">
                    {{ $company->name }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    {{ $company->address }}, {{ $company->city }}, {{ $company->state }} - {{ $company->pincode }}
                </div>
                <div style="margin-top: 6px;">
                    <span style="display: inline-block; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 2px 10px; font-size: 10px; font-weight: 700; color: #334155; font-family: 'JetBrains Mono', monospace;">
                        GSTIN: <strong style="color: #0f172a;">{{ $company->gstin ?? '10DYFPA2189J1ZO' }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Meta Box (Rounded Pill Container) -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 18px; margin-bottom: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 3px 0;">
                <span style="font-size: 11.5px; color: #475569; font-weight: 500;">Invoice No:</span>
                <span class="mono" style="font-size: 12px; font-weight: 800; color: #0f172a;">#{{ $invoice->invoice_number }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 3px 0;">
                <span style="font-size: 11.5px; color: #475569; font-weight: 500;">Date:</span>
                <span class="mono" style="font-size: 12px; font-weight: 700; color: #0f172a;">{{ $invoice->invoice_date->format('d-m-Y') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 3px 0;">
                <span style="font-size: 11.5px; color: #475569; font-weight: 500;">Status:</span>
                <span style="font-size: 12px; font-weight: 900; color: {{ $invoice->status === 'paid' ? '#16a34a' : '#d97706' }}; text-transform: uppercase;">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>
        </div>

        <!-- Invoiced To Block -->
        <div style="margin-bottom: 22px;">
            <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                INVOICED TO
            </div>
            <div style="font-size: 17px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: -0.3px;">
                {{ $customer->name }}
            </div>
            <div style="font-size: 11px; color: #475569; line-height: 1.5; margin-top: 3px;">
                {{ $customer->billing_address ?? $customer->address }}<br>
                {{ $customer->city ? $customer->city . ', ' : '' }}{{ $customer->state }}
            </div>
            <div style="font-size: 11px; font-weight: 700; color: #0f172a; margin-top: 4px;">
                GSTIN: <span class="mono">{{ $customer->gstin ?? 'URP' }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px;">
            <thead>
                <tr style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; text-align: left;">
                    <th style="padding: 10px 12px; font-size: 10.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">DESCRIPTION</th>
                    <th style="padding: 10px 12px; font-size: 10.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; text-align: right; width: 120px;">RATE</th>
                    <th style="padding: 10px 12px; font-size: 10.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; width: 90px;">GST %</th>
                    <th style="padding: 10px 12px; font-size: 10.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; text-align: right; width: 130px;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-size: 11.5px; color: #0f172a; font-weight: 600;">
                        {{ $item->description }}
                        @if($item->domain_name || $item->service_period_start)
                        <div style="font-size: 9.5px; color: #16a34a; font-weight: 500; margin-top: 2px;">
                            @if($item->domain_name) Domain: {{ $item->domain_name }} @endif
                            @if($item->service_period_start) | Period: {{ $item->service_period_start->format('d/m/Y') }} - {{ $item->service_period_end ? $item->service_period_end->format('d/m/Y') : '' }} @endif
                        </div>
                        @endif
                    </td>
                    <td style="padding: 12px; font-size: 11.5px; text-align: right; color: #0f172a;" class="mono">
                        ₹{{ number_format($item->rate, 2) }}
                    </td>
                    <td style="padding: 12px; font-size: 11.5px; text-align: center; color: #475569;" class="mono">
                        {{ (float)$item->gst_percent }}%
                    </td>
                    <td style="padding: 12px; font-size: 11.5px; text-align: right; color: #0f172a; font-weight: 800;" class="mono">
                        ₹{{ number_format($item->total_amount, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Block -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
            <div style="width: 250px; font-size: 11.5px; line-height: 1.8;">
                <div style="display: flex; justify-content: space-between; color: #475569;">
                    <span>Taxable Amount</span>
                    <span class="mono" style="font-weight: 700; color: #0f172a;">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; color: #475569;">
                    <span>GST Amount</span>
                    <span class="mono" style="font-weight: 700; color: #0f172a;">₹{{ number_format($invoice->gst_amount, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 2px solid #0f172a; padding-top: 6px; margin-top: 4px; font-size: 16px; font-weight: 900; color: #0f172a;">
                    <span>Grand Total</span>
                    <span class="mono">₹{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div style="margin-top: 24px; margin-bottom: 24px;">
            <div style="font-size: 13px; font-weight: 900; color: #0f172a; margin-bottom: 8px; letter-spacing: -0.2px;">
                Transaction History
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 10.5px;">
                <thead>
                    <tr style="border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; background: #fafafa; text-align: left;">
                        <th style="padding: 8px 10px; font-weight: 800; color: #475569; text-transform: uppercase;">DATE</th>
                        <th style="padding: 8px 10px; font-weight: 800; color: #475569; text-transform: uppercase;">PAYMENT METHOD</th>
                        <th style="padding: 8px 10px; font-weight: 800; color: #475569; text-transform: uppercase;">STATUS</th>
                        <th style="padding: 8px 10px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: right;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->transactions as $trx)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 9px 10px; font-weight: 700; color: #0f172a;" class="mono">{{ $trx->transaction_date ? $trx->transaction_date->format('d-m-Y') : $invoice->invoice_date->format('d-m-Y') }}</td>
                        <td style="padding: 9px 10px;">
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 14px; padding: 3px 9px; font-size: 9.5px; font-weight: 700; color: #334155;">
                                💳 {{ strtoupper($trx->payment_method ?? 'UPI / Digital Payment') }}
                            </span>
                        </td>
                        <td style="padding: 9px 10px; font-weight: 800; color: #16a34a;">
                            ✓ Paid & Verified
                        </td>
                        <td style="padding: 9px 10px; text-align: right; font-weight: 800; color: #0f172a;" class="mono">
                            ₹{{ number_format($trx->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 9px 10px; font-weight: 700; color: #0f172a;" class="mono">{{ $invoice->invoice_date->format('d-m-Y') }}</td>
                        <td style="padding: 9px 10px;">
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 14px; padding: 3px 9px; font-size: 9.5px; font-weight: 700; color: #334155;">
                                💳 UPI / Digital Payment
                            </span>
                        </td>
                        <td style="padding: 9px 10px; font-weight: 800; color: {{ $invoice->status === 'paid' ? '#16a34a' : '#d97706' }};">
                            {{ $invoice->status === 'paid' ? '✓ Paid & Verified' : 'Pending Payment' }}
                        </td>
                        <td style="padding: 9px 10px; text-align: right; font-weight: 800; color: #0f172a;" class="mono">
                            ₹{{ number_format($invoice->paid_amount > 0 ? $invoice->paid_amount : $invoice->total_amount, 2) }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Green Studio Clean Footer -->
        <div style="margin-top: 36px; border-top: 1px solid #e2e8f0; padding-top: 16px; text-align: center; font-size: 10.5px; color: #64748b;">
            This is a computer generated invoice no signature required.
        </div>

    </div>

</div>

</body>
</html>
