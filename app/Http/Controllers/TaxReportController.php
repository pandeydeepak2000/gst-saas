<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxReportController extends Controller
{
    /**
     * Show GSTR-1 Summary Dashboard
     */
    public function gstr1(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('reports'), 403, 'Access denied: You do not have permission to view GSTR-1 Tax Reports.');
        $company = auth()->user()->company;
        $month = $request->get('month', now()->format('Y-m'));

        $startDate = $month . '-01';
        $endDate = date('Y-t', strtotime($startDate)) . '-' . date('t', strtotime($startDate));

        // Base invoices query (tax invoices only, excluding draft/cancelled)
        $invoicesQuery = Invoice::where('type', '!=', 'proforma')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with(['customer', 'items']);

        $invoices = (clone $invoicesQuery)->latest('invoice_date')->get();

        // B2B: Invoices with Registered Customers (Having GSTIN)
        $b2bInvoices = $invoices->filter(fn($inv) => !empty($inv->customer?->gstin));

        // B2C: Unregistered / Retail Invoices (No GSTIN)
        $b2cInvoices = $invoices->filter(fn($inv) => empty($inv->customer?->gstin));

        // Table 12: HSN / SAC Code Summary
        $hsnSummary = InvoiceItem::whereHas('invoice', function ($q) use ($startDate, $endDate) {
            $q->where('type', '!=', 'proforma')
              ->whereNotIn('status', ['draft', 'cancelled'])
              ->whereBetween('invoice_date', [$startDate, $endDate]);
        })
        ->selectRaw('
            hsn_sac,
            description,
            SUM(quantity) as total_qty,
            SUM(taxable_amount) as total_taxable,
            SUM(cgst_amount) as total_cgst,
            SUM(sgst_amount) as total_sgst,
            SUM(igst_amount) as total_igst,
            SUM(line_total) as grand_total
        ')
        ->groupBy('hsn_sac', 'description')
        ->orderByDesc('total_taxable')
        ->get();

        $metrics = [
            'total_invoices' => $invoices->count(),
            'total_taxable'  => $invoices->sum('taxable_amount'),
            'total_cgst'     => $invoices->sum('cgst_amount'),
            'total_sgst'     => $invoices->sum('sgst_amount'),
            'total_igst'     => $invoices->sum('igst_amount'),
            'grand_total'    => $invoices->sum('total_amount'),
            'b2b_count'      => $b2bInvoices->count(),
            'b2b_taxable'    => $b2bInvoices->sum('taxable_amount'),
            'b2c_count'      => $b2cInvoices->count(),
            'b2c_taxable'    => $b2cInvoices->sum('taxable_amount'),
        ];

        return view('reports.gstr1', compact('company', 'month', 'b2bInvoices', 'b2cInvoices', 'hsnSummary', 'metrics'));
    }

    /**
     * Download GSTR-1 B2B / B2C / HSN CSV for Chartered Accountants
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = $month . '-01';
        $endDate = date('Y-t', strtotime($startDate)) . '-' . date('t', strtotime($startDate));

        $invoices = Invoice::where('type', '!=', 'proforma')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with(['customer', 'items'])
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="GSTR1_Export_' . $month . '.csv"',
        ];

        return response()->stream(function () use ($invoices) {
            $handle = fopen('php://output', 'w');

            // B2B Section
            fputcsv($handle, ['--- GSTR-1 B2B TAX INVOICES ---']);
            fputcsv($handle, ['GSTIN/UIN of Recipient', 'Receiver Name', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Reverse Charge', 'Invoice Type', 'Rate (%)', 'Taxable Value', 'Cess Amount']);

            foreach ($invoices->filter(fn($i) => !empty($i->customer?->gstin)) as $inv) {
                fputcsv($handle, [
                    $inv->customer->gstin,
                    $inv->customer->name,
                    $inv->invoice_number,
                    $inv->invoice_date->format('d-M-Y'),
                    number_format($inv->total_amount, 2, '.', ''),
                    $inv->customer->state ?? $inv->company->state,
                    'N',
                    'Regular',
                    ($inv->tax_mode === 'simple' ? '18' : 'Standard'),
                    number_format($inv->taxable_amount, 2, '.', ''),
                    '0.00'
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['--- GSTR-1 B2C (SMALL) INVOICES ---']);
            fputcsv($handle, ['Type', 'Place of Supply', 'Rate (%)', 'Taxable Value', 'Cess Amount', 'Invoice Number', 'Invoice Date']);

            foreach ($invoices->filter(fn($i) => empty($i->customer?->gstin)) as $inv) {
                fputcsv($handle, [
                    'OE (Other than E-Commerce)',
                    $inv->customer->state ?? $inv->company->state,
                    '18',
                    number_format($inv->taxable_amount, 2, '.', ''),
                    '0.00',
                    $inv->invoice_number,
                    $inv->invoice_date->format('d-M-Y')
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}