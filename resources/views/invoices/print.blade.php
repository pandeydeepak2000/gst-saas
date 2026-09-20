<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice_{{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
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
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
        }
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 14mm 16mm 12mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 6px 8px;
        }
        th {
            background: #0f172a;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .no-print {
            text-align: center;
            margin-bottom: 15px;
        }
        .btn-print {
            padding: 8px 20px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .page {
                box-shadow: none;
                width: 100%;
                min-height: 100vh;
                padding: 12mm 14mm 10mm;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    <span style="font-size: 12px; color: #64748b; margin-left: 10px;">(A4 Single-Page Proportioned Format)</span>
</div>

<div class="page">
    <div>
        <!-- Company Header & Invoice Details -->
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 12px;">
            <div>
                <span style="font-size: 9px; font-weight: 800; letter-spacing: 1px; color: #4f46e5; text-transform: uppercase;">TAX INVOICE</span>
                <h1 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $company->name }}</h1>
                <p style="color: #475569; font-size: 10px; max-width: 320px; margin-top: 2px;">
                    {{ $company->address ?: 'Head Office' }}, {{ $company->city }}, {{ $company->state }} {{ $company->pincode ? '- ' . $company->pincode : '' }}
                </p>
                <div style="font-size: 10px; color: #334155; margin-top: 4px;" class="mono">
                    @if($company->gstin)<div>GSTIN: <strong>{{ $company->gstin }}</strong></div>@endif
                    @if($company->phone)<div>Phone: {{ $company->phone }}</div>@endif
                </div>
            </div>

            <div style="text-align: right;">
                <div class="mono" style="font-size: 18px; font-weight: 800; color: #4f46e5;">{{ $invoice->invoice_number }}</div>
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">
                    Invoice Date: <strong>{{ $invoice->invoice_date->format('d M, Y') }}</strong>
                </div>
                @if($invoice->due_date)
                <div style="font-size: 10px; color: #475569;">
                    Due Date: <strong>{{ $invoice->due_date->format('d M, Y') }}</strong>
                </div>
                @endif
                <div style="margin-top: 6px;">
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; background: {{ $invoice->status === 'paid' ? '#dcfce7; color: #166534;' : '#fef3c7; color: #92400e;' }}">
                        {{ $invoice->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bill To & Supply Info -->
        <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; margin-bottom: 14px;">
            <div style="width: 55%;">
                <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #64748b;">BILLED TO:</span>
                <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $invoice->customer->name ?? 'Direct Counter Sale' }}</div>
                @if($invoice->customer && $invoice->customer->company_name)
                    <div style="font-size: 10px; color: #475569;">{{ $invoice->customer->company_name }}</div>
                @endif
                <div style="font-size: 10px; color: #475569; margin-top: 2px;">{{ $invoice->customer->billing_address ?? '' }}</div>
                @if($invoice->customer && $invoice->customer->gstin)
                    <div class="mono" style="font-size: 10px; color: #1e293b; margin-top: 2px;">GSTIN: <strong>{{ $invoice->customer->gstin }}</strong></div>
                @endif
            </div>

            <div style="width: 40%; text-align: right; font-size: 10px;">
                <div style="color: #64748b; font-weight: 600;">PLACE OF SUPPLY:</div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 2px;">
                    {{ $invoice->sale_type === 'LOCAL' ? 'Intra-State (' . $company->state . ')' : 'Inter-State (IGST)' }}
                </div>
                @if($invoice->payment_method)
                <div style="margin-top: 4px; color: #475569;">
                    Payment Method: <strong>{{ $invoice->payment_method }}</strong>
                </div>
                @endif
            </div>
        </div>
        <!-- Items Table -->
        <table style="border: 1px solid #cbd5e1; margin-bottom: 14px;">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">#</th>
                    <th style="text-align: left;">Item Description</th>
                    <th style="width: 50px; text-align: center;">HSN</th>
                    <th style="width: 45px; text-align: center;">Qty</th>
                    <th style="width: 65px; text-align: right;">Rate</th>
                    <th style="width: 45px; text-align: center;">GST %</th>
                    <th style="width: 75px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody class="mono" style="font-size: 10px;">
                @foreach($invoice->items as $idx => $item)
                <tr style="border-bottom: 1px solid #e2e8f0; {{ $idx % 2 === 1 ? 'background: #f8fafc;' : '' }}">
                    <td style="text-align: center; color: #94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 500; color: #0f172a;">
    <div>{{ $item->description }}</div>
    @if(!empty($item->domain_name) || !empty($item->service_period_start))
    <div style="font-size: 8.5px; color: #4338ca; margin-top: 3px; font-weight: 600;">
        @if(!empty($item->domain_name))
            <span style="background: #e0e7ff; padding: 1px 5px; border-radius: 3px; color: #3730a3; display: inline-block;">🌐 {{ $item->domain_name }}</span>
        @endif
        @if(!empty($item->service_period_start))
            <span style="color: #64748b; margin-left: 4px;">Period: {{ \Carbon\Carbon::parse($item->service_period_start)->format('d M Y') }} to {{ \Carbon\Carbon::parse($item->service_period_end)->format('d M Y') }}</span>
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

        <!-- Tax Breakdown & Totals Summary -->
        <div style="display: flex; justify-content: space-between; gap: 15px; margin-bottom: 16px;">
            <div style="width: 55%; font-size: 9.5px; color: #475569;">
                @if($invoice->notes)
                    <strong style="color: #0f172a; display: block; margin-bottom: 2px;">Notes / Remarks:</strong>
                    <div style="white-space: pre-line; background: #f8fafc; padding: 6px 8px; border-radius: 4px; border: 1px solid #e2e8f0;">{{ $invoice->notes }}</div>
                @endif
                <div style="margin-top: 8px;">
                    <strong style="color: #0f172a;">Terms & Conditions:</strong>
                    <div style="margin-top: 2px;">1. Payment due within specified period. 2. Subject to company jurisdiction.</div>
                </div>
            </div>

            <div style="width: 42%; font-size: 10.5px;" class="mono">
                <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #cbd5e1;">
                    <span style="color: #64748b;">Taxable Value:</span>
                    <span style="font-weight: 600;">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
                </div>

                @if($invoice->effective_tax_mode === 'simple')
                    <!-- Simple GST Single Line -->
                    <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dashed #cbd5e1; color: #4f46e5; font-weight: 700;">
                        <span>GST (Total 18%):</span>
                        <span>₹{{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</span>
                    </div>
                @else
                    <!-- Detailed Tax Split -->
                    @if($invoice->sale_type === 'LOCAL')
                        <div style="display: flex; justify-content: space-between; padding: 3px 0; color: #059669;">
                            <span>CGST:</span>
                            <span>₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px dashed #cbd5e1; color: #059669;">
                            <span>SGST:</span>
                            <span>₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                        </div>
                    @else
                        <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #cbd5e1; color: #7c3aed;">
                            <span>IGST (Integrated):</span>
                            <span>₹{{ number_format($invoice->igst_amount, 2) }}</span>
                        </div>
                    @endif
                @endif

                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-top: 2px solid #0f172a; font-size: 13px; font-weight: 800; color: #0f172a;">
                    <span>Grand Total:</span>
                    <span style="color: #4f46e5;">₹{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Footer: Bank Details, UPI QR, and Signature -->
    <div style="border-top: 1.5px solid #0f172a; padding-top: 10px; margin-top: auto;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            
            <!-- Bank & UPI Information -->
            <div style="font-size: 9.5px; color: #334155; line-height: 1.5;">
                <strong style="color: #0f172a; font-size: 10px; text-transform: uppercase;">Bank & UPI Settlement Details:</strong>
                <div class="mono" style="margin-top: 2px;">
                    @if($company->bank_name)<div>Bank Name: <strong>{{ $company->bank_name }}</strong></div>@endif
                    @if($company->bank_account_number)<div>A/C Number: <strong>{{ $company->bank_account_number }}</strong></div>@endif
                    @if($company->bank_ifsc)<div>IFSC Code: <strong>{{ $company->bank_ifsc }}</strong></div>@endif
                    @if($company->upi_id)<div>UPI VPA: <strong style="color: #4f46e5;">{{ $company->upi_id }}</strong></div>@endif
                </div>
            </div>

            <!-- UPI Dynamic Payment QR Code -->
            @if($company->upi_id)
            <div style="text-align: center;">
                @php
                    $upiPayload = "upi://pay?pa=" . urlencode($company->upi_id) . "&pn=" . urlencode($company->name) . "&am=" . $invoice->total_amount . "&cu=INR&tn=" . urlencode("Invoice " . $invoice->invoice_number);
                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=85x85&data=" . urlencode($upiPayload);
                @endphp
                <img src="{{ $qrUrl }}" alt="UPI QR" style="width: 70px; height: 70px; border: 1px solid #cbd5e1; padding: 2px; border-radius: 4px; display: inline-block;">
                <div style="font-size: 8px; color: #64748b; margin-top: 2px; font-weight: 600;">Scan with any UPI App</div>
            </div>
            @endif

            <!-- Authorized Signature -->
            <div style="text-align: right; min-width: 140px;">
                <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">For {{ $company->name }}</div>
                <div style="height: 40px;"></div>
                <div style="border-top: 1px solid #94a3b8; padding-top: 3px; font-weight: 700; font-size: 9.5px; color: #0f172a;">
                    Authorized Signatory
                </div>
            </div>

        </div>

        <div style="text-align: center; margin-top: 10px; font-size: 8.5px; color: #94a3b8;">
            This is a Computer Generated Tax Invoice powered by GST-SaaS Cloud Platform.
        </div>
    </div>
</div>

</body>
</html>
