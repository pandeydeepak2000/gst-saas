<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTransaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $query = Invoice::with('customer');

        if ($tab === 'trash') {
            $query = Invoice::onlyTrashed()->with('customer');
        } elseif ($tab === 'paid') {
            $query->where('status', 'paid');
        } elseif ($tab === 'unpaid') {
            $query->where('status', 'unpaid');
        } elseif ($tab === 'partially_paid') {
            $query->where('status', 'partially_paid');
        } elseif ($tab === 'proforma') {
            $query->where('type', 'proforma');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('company_name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        $invoices = $query->latest('id')->paginate(15);
        $trashCount = Invoice::onlyTrashed()->count();
        $proformaCount = Invoice::where('type', 'proforma')->count();

        return view('invoices.index', compact('invoices', 'tab', 'trashCount', 'proformaCount'));
    }

    public function create()
    {
        $company = auth()->user()->company;
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $suggestedNumber = $company->generateNextInvoiceNumber();

        return view('invoices.create', compact('company', 'customers', 'products', 'suggestedNumber'));
    }

    public function store(Request $request)
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'customer_id'    => ['required', Rule::exists('customers', 'id')->where('company_id', $company->id)->whereNull('deleted_at')],
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
            ],
            'type'           => ['nullable', 'in:tax_invoice,proforma'],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'sale_type'      => ['required', 'in:LOCAL,CENTRAL'],
            'tax_mode'       => ['required', 'in:simple,detailed'],
            'status'         => ['required', 'in:draft,unpaid,paid,partially_paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string'],

            'items'                        => ['required', 'array', 'min:1'],
            'items.*.description'          => ['required', 'string'],
            'items.*.domain_name'          => ['nullable', 'string', 'max:255'],
            'items.*.service_period_start' => ['nullable', 'date'],
            'items.*.service_period_end'   => ['nullable', 'date'],
            'items.*.billing_cycle'        => ['nullable', 'string', 'max:50'],
            'items.*.hsn_sac'              => ['nullable', 'string', 'max:20'],
            'items.*.quantity'             => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'                 => ['required', 'string', 'max:20'],
            'items.*.rate'                 => ['required', 'numeric', 'min:0'],
            'items.*.gst_percent'          => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            $isLocal = ($validated['sale_type'] === 'LOCAL');

            $invoice = Invoice::create([
                'company_id'      => $company->id,
                'customer_id'     => $validated['customer_id'],
                'created_by'      => auth()->id(),
                'invoice_number'  => trim($validated['invoice_number']),
                'public_uuid'     => (string) Str::uuid(),
                'type'            => $validated['type'] ?? 'tax_invoice',
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'],
                'sale_type'       => $validated['sale_type'],
                'tax_mode'        => $validated['tax_mode'],
                'status'          => $validated['status'],
                'payment_method'  => $validated['payment_method'] ?? null,
                'notes'           => $validated['notes'] ?? null,
                'taxable_amount'  => 0,
                'cgst_amount'     => 0,
                'sgst_amount'     => 0,
                'igst_amount'     => 0,
                'total_amount'    => 0,
                'paid_amount'     => 0,
                'balance_amount'  => 0,
            ]);

            $totalTaxable = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;
            $grandTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = (float)$itemData['quantity'];
                $rate = (float)$itemData['rate'];
                $gstRate = (float)$itemData['gst_percent'];

                $taxable = round($qty * $rate, 2);
                $taxAmount = round($taxable * ($gstRate / 100), 2);

                $cgst = 0;
                $sgst = 0;
                $igst = 0;

                if ($isLocal) {
                    $cgst = round($taxAmount / 2, 2);
                    $sgst = round($taxAmount - $cgst, 2);
                } else {
                    $igst = $taxAmount;
                }

                $lineTotal = $taxable + $taxAmount;

                $totalTaxable += $taxable;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id'           => $invoice->id,
                    'description'          => $itemData['description'],
                    'domain_name'          => $itemData['domain_name'] ?? null,
                    'service_period_start' => $itemData['service_period_start'] ?? null,
                    'service_period_end'   => $itemData['service_period_end'] ?? null,
                    'billing_cycle'        => $itemData['billing_cycle'] ?? null,
                    'hsn_sac'              => $itemData['hsn_sac'] ?? null,
                    'quantity'             => $qty,
                    'unit'                 => $itemData['unit'] ?? 'Pcs',
                    'rate'                 => $rate,
                    'gst_percent'          => $gstRate,
                    'taxable_amount'       => $taxable,
                    'cgst_amount'          => $cgst,
                    'sgst_amount'          => $sgst,
                    'igst_amount'          => $igst,
                    'line_total'           => $lineTotal,
                ]);
            }

            $paidAmount = 0;
            if ($validated['status'] === 'paid') {
                $paidAmount = $grandTotal;
            }

            $invoice->update([
                'taxable_amount' => $totalTaxable,
                'cgst_amount'    => $totalCgst,
                'sgst_amount'    => $totalSgst,
                'igst_amount'    => $totalIgst,
                'total_amount'   => $grandTotal,
                'paid_amount'    => $paidAmount,
                'balance_amount' => max(0, $grandTotal - $paidAmount),
            ]);

            // If marked as paid on creation, record a transaction
            if ($paidAmount > 0) {
                InvoiceTransaction::create([
                    'company_id'     => $company->id,
                    'invoice_id'     => $invoice->id,
                    'gateway'        => $invoice->payment_method ?: 'Cash',
                    'payment_method' => strtolower($invoice->payment_method ?: 'cash'),
                    'transaction_id' => 'INIT-' . strtoupper(uniqid()),
                    'amount'         => $paidAmount,
                    'paid_at'        => now(),
                    'notes'          => 'Initial payment recorded at creation',
                ]);
            }

            ActivityLog::log('create', 'invoice', "Created Invoice #{$invoice->invoice_number} for ₹" . number_format($grandTotal, 2), $invoice->id);
            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', "Invoice #{$invoice->invoice_number} created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::withTrashed()->where('company_id', $companyId)->with(['customer', 'items', 'creator', 'transactions', 'company'])->findOrFail($id);

        $company = $invoice->company;
        $customer = $invoice->customer;

        // Dynamic WhatsApp Share Link
        $cleanPhone = preg_replace('/[^0-9]/', '', $invoice->customer?->phone ?? '');
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $publicUrl = route('public.invoice.show', $invoice->public_uuid);

        $defaultTemplate = "Dear {customer_name},\nYour invoice #{invoice_number} from {company_name} for ₹{total_amount} is generated.\nBalance Due: ₹{balance_amount}\nDue Date: {due_date}\n\nView & Pay Online:\n{public_url}\n\nThank you for your business!";
        $template = $company->whatsapp_template ?: $defaultTemplate;

        $message = str_replace(
            ['{customer_name}', '{invoice_number}', '{total_amount}', '{balance_amount}', '{due_date}', '{public_url}', '{company_name}'],
            [
                $invoice->customer?->name,
                $invoice->invoice_number,
                number_format($invoice->total_amount, 2),
                number_format($invoice->balance_amount, 2),
                $invoice->due_date ? $invoice->due_date->format('d-M-Y') : 'Due on Receipt',
                $publicUrl,
                $company->name
            ],
            $template
        );

        $whatsappUrl = !empty($cleanPhone)
            ? "https://wa.me/{$cleanPhone}?text=" . rawurlencode($message)
            : "https://wa.me/?text=" . rawurlencode($message);

        // Dynamic Zero-Fee UPI Payment QR Code
        $upiUrl = null;
        if ($company->enable_upi_qr && !empty($company->upi_id) && $invoice->balance_amount > 0) {
            $pa = rawurlencode($company->upi_id);
            $pn = rawurlencode($company->upi_name ?: $company->name);
            $am = number_format($invoice->balance_amount, 2, '.', '');
            $tr = rawurlencode($invoice->invoice_number);
            $tn = rawurlencode('Invoice ' . $invoice->invoice_number);
            $upiUrl = "upi://pay?pa={$pa}&pn={$pn}&am={$am}&tr={$tr}&tn={$tn}&cu=INR";
        }

        return view('invoices.show', compact('invoice', 'company', 'whatsappUrl', 'upiUrl', 'publicUrl'));
    }

    public function edit($id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::where('company_id', $companyId)->with(['customer', 'items'])->findOrFail($id);
        $company = auth()->user()->company;
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        $existingItems = $invoice->items->map(function($i) {
            return [
                'description'          => $i->description,
                'hsn_sac'              => $i->hsn_sac ?? '',
                'quantity'             => (float) $i->quantity,
                'unit'                 => $i->unit,
                'rate'                 => (float) $i->rate,
                'gst_percent'          => (float) $i->gst_percent,
                'domain_name'          => $i->domain_name ?? '',
                'service_period_start' => $i->service_period_start ? (\Carbon\Carbon::parse($i->service_period_start)->format('Y-m-d')) : '',
                'service_period_end'   => $i->service_period_end ? (\Carbon\Carbon::parse($i->service_period_end)->format('Y-m-d')) : '',
                'billing_cycle'        => $i->billing_cycle ?? '',
            ];
        });

        return view('invoices.edit', compact('invoice', 'company', 'customers', 'products', 'existingItems'));
    }

    public function update(Request $request, $id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);
        $company = auth()->user()->company;

        $validated = $request->validate([
            'customer_id'    => ['required', Rule::exists('customers', 'id')->where('company_id', $company->id)->whereNull('deleted_at')],
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
                    ->ignore($invoice->id)
            ],
            'type'           => ['nullable', 'in:tax_invoice,proforma'],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'sale_type'      => ['required', 'in:LOCAL,CENTRAL'],
            'tax_mode'       => ['required', 'in:simple,detailed'],
            'status'         => ['required', 'in:draft,unpaid,paid,partially_paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string'],

            'items'                        => ['required', 'array', 'min:1'],
            'items.*.description'          => ['required', 'string'],
            'items.*.domain_name'          => ['nullable', 'string', 'max:255'],
            'items.*.service_period_start' => ['nullable', 'date'],
            'items.*.service_period_end'   => ['nullable', 'date'],
            'items.*.billing_cycle'        => ['nullable', 'string', 'max:50'],
            'items.*.hsn_sac'              => ['nullable', 'string', 'max:20'],
            'items.*.quantity'             => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'                 => ['required', 'string', 'max:20'],
            'items.*.rate'                 => ['required', 'numeric', 'min:0'],
            'items.*.gst_percent'          => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            $isLocal = ($validated['sale_type'] === 'LOCAL');

            $invoice->update([
                'customer_id'     => $validated['customer_id'],
                'invoice_number'  => trim($validated['invoice_number']),
                'type'            => $validated['type'] ?? $invoice->type,
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'],
                'sale_type'       => $validated['sale_type'],
                'tax_mode'        => $validated['tax_mode'],
                'status'          => $validated['status'],
                'payment_method'  => $validated['payment_method'] ?? null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            $invoice->items()->delete();

            $totalTaxable = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;
            $grandTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = (float)$itemData['quantity'];
                $rate = (float)$itemData['rate'];
                $gstRate = (float)$itemData['gst_percent'];

                $taxable = round($qty * $rate, 2);
                $taxAmount = round($taxable * ($gstRate / 100), 2);

                $cgst = 0;
                $sgst = 0;
                $igst = 0;

                if ($isLocal) {
                    $cgst = round($taxAmount / 2, 2);
                    $sgst = round($taxAmount - $cgst, 2);
                } else {
                    $igst = $taxAmount;
                }

                $lineTotal = $taxable + $taxAmount;

                $totalTaxable += $taxable;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id'           => $invoice->id,
                    'description'          => $itemData['description'],
                    'domain_name'          => $itemData['domain_name'] ?? null,
                    'service_period_start' => $itemData['service_period_start'] ?? null,
                    'service_period_end'   => $itemData['service_period_end'] ?? null,
                    'billing_cycle'        => $itemData['billing_cycle'] ?? null,
                    'hsn_sac'              => $itemData['hsn_sac'] ?? null,
                    'quantity'             => $qty,
                    'unit'                 => $itemData['unit'] ?? 'Pcs',
                    'rate'                 => $rate,
                    'gst_percent'          => $gstRate,
                    'taxable_amount'       => $taxable,
                    'cgst_amount'          => $cgst,
                    'sgst_amount'          => $sgst,
                    'igst_amount'          => $igst,
                    'line_total'           => $lineTotal,
                ]);
            }

            $invoice->update([
                'taxable_amount' => $totalTaxable,
                'cgst_amount'    => $totalCgst,
                'sgst_amount'    => $totalSgst,
                'igst_amount'    => $totalIgst,
                'total_amount'   => $grandTotal,
            ]);

            $invoice->recalculatePaymentStatus();

            ActivityLog::log('update', 'invoice', "Updated Invoice #{$invoice->invoice_number} details & items.", $invoice->id);
            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', "Invoice #{$invoice->invoice_number} updated successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * 1-Click Convert Proforma Quotation to Official Tax Invoice
     */
    public function convertToTaxInvoice($id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);
        $company = auth()->user()->company;

        if ($invoice->type !== 'proforma') {
            return back()->with('info', 'This invoice is already an official Tax Invoice.');
        }

        $oldNumber = $invoice->invoice_number;
        $newNumber = $company->generateNextInvoiceNumber();

        $invoice->update([
            'type'           => 'tax_invoice',
            'invoice_number' => $newNumber,
        ]);

        ActivityLog::log('update', 'invoice', "Converted Proforma #{$oldNumber} into official Tax Invoice #{$newNumber}.", $invoice->id);

        return back()->with('success', "Proforma invoice successfully converted to official Tax Invoice #{$newNumber}!");
    }

    /**
     * Quick 1-click mark as fully paid
     */
    public function markAsPaid(Request $request, $id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);
        $balance = $invoice->balance_amount > 0 ? $invoice->balance_amount : $invoice->total_amount;

        InvoiceTransaction::create([
            'company_id'     => $invoice->company_id,
            'invoice_id'     => $invoice->id,
            'gateway'        => 'Manual Full Settlement',
            'payment_method' => $request->payment_method ?? 'cash',
            'transaction_id' => 'FULL-' . strtoupper(uniqid()),
            'amount'         => $balance,
            'paid_at'        => now(),
            'notes'          => 'Marked as fully paid by admin',
        ]);

        $invoice->recalculatePaymentStatus();

        ActivityLog::log('create', 'payment', "Marked Invoice #{$invoice->invoice_number} as fully paid.", $invoice->id);

        return back()->with('success', "Invoice #{$invoice->invoice_number} marked as fully paid!");
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->delete();

        ActivityLog::log('trash', 'invoice', "Moved invoice #{$num} to Trash.", $id);
        return back()->with('warning', "Invoice #{$num} moved to Trash. You can restore it anytime from the Trash tab.");
    }

    public function restore($id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::onlyTrashed()->where('company_id', $companyId)->findOrFail($id);
        $invoice->restore();

        ActivityLog::log('restore', 'invoice', "Restored invoice #{$invoice->invoice_number} from Trash.", $id);
        return back()->with('success', "Invoice #{$invoice->invoice_number} restored successfully!");
    }

    public function forceDelete($id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::onlyTrashed()->where('company_id', $companyId)->findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->items()->delete();
        $invoice->forceDelete();

        ActivityLog::log('force_delete', 'invoice', "Permanently deleted invoice #{$num}.");
        return back()->with('danger', "Invoice #{$num} permanently deleted.");
    }

    public function print($id)
    {
        $companyId = auth()->user()->company_id;
        $invoice = Invoice::withTrashed()->where('company_id', $companyId)->with(['customer', 'items', 'company', 'transactions'])->findOrFail($id);
        $company = $invoice->company;
        $customer = $invoice->customer;

        // UPI QR URL for print
        $upiUrl = null;
        if ($company->enable_upi_qr && !empty($company->upi_id) && $invoice->balance_amount > 0) {
            $pa = rawurlencode($company->upi_id);
            $pn = rawurlencode($company->upi_name ?: $company->name);
            $am = number_format($invoice->balance_amount, 2, '.', '');
            $tr = rawurlencode($invoice->invoice_number);
            $tn = rawurlencode('Invoice ' . $invoice->invoice_number);
            $upiUrl = "upi://pay?pa={$pa}&pn={$pn}&am={$am}&tr={$tr}&tn={$tn}&cu=INR";
        }

        return view('invoices.print', compact('invoice', 'company', 'customer', 'upiUrl'));
    }
}