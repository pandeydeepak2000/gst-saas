<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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

        return view('invoices.index', compact('invoices', 'tab', 'trashCount'));
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
            'customer_id'    => ['required', 'exists:customers,id'],
            // Company can edit invoice number freely!
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
            ],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'sale_type'      => ['required', 'in:LOCAL,CENTRAL'],
            'tax_mode'       => ['required', 'in:simple,detailed'],
            'status'         => ['required', 'in:draft,unpaid,paid,partially_paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string'],

            // Items validation
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'          => ['required', 'string'],
            'items.*.domain_name'          => ['nullable', 'string', 'max:255'],
            'items.*.service_period_start' => ['nullable', 'date'],
            'items.*.service_period_end'   => ['nullable', 'date'],
            'items.*.billing_cycle'        => ['nullable', 'string', 'max:50'],
            'items.*.hsn_sac'              => ['nullable', 'string', 'max:20'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'          => ['required', 'string', 'max:20'],
            'items.*.rate'          => ['required', 'numeric', 'min:0'],
            'items.*.gst_percent'   => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'invoice_number.unique' => 'This invoice number already exists for your company. Please choose another number.',
            'items.min' => 'Please add at least one line item to the invoice.',
        ]);

        DB::beginTransaction();
        try {
            $totalTaxable = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;
            $grandTotal = 0;

            $isLocal = ($validated['sale_type'] === 'LOCAL');

            $invoice = Invoice::create([
                'company_id'      => $company->id,
                'customer_id'     => $validated['customer_id'],
                'created_by'      => auth()->id(),
                'invoice_number'  => trim($validated['invoice_number']),
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'],
                'sale_type'       => $validated['sale_type'],
                'tax_mode'        => $validated['tax_mode'],
                'status'          => $validated['status'],
                'payment_method'  => $validated['payment_method'] ?? null,
                'notes'           => $validated['notes'] ?? null,
            ]);

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
                    $sgst = round($taxAmount - $cgst, 2); // Exact split without fractional penny loss
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
                    'quantity'       => $qty,
                    'unit'           => $itemData['unit'] ?? 'Pcs',
                    'rate'           => $rate,
                    'gst_percent'    => $gstRate,
                    'taxable_amount' => $taxable,
                    'cgst_amount'    => $cgst,
                    'sgst_amount'    => $sgst,
                    'igst_amount'    => $igst,
                    'line_total'     => $lineTotal,
                ]);
            }

            $invoice->update([
                'taxable_amount' => $totalTaxable,
                'cgst_amount'    => $totalCgst,
                'sgst_amount'    => $totalSgst,
                'igst_amount'    => $totalIgst,
                'total_amount'   => $grandTotal,
            ]);

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
        $invoice = Invoice::withTrashed()->with(['customer', 'items', 'creator'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $company = auth()->user()->company;
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'company', 'customers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $company = auth()->user()->company;

        $validated = $request->validate([
            'customer_id'    => ['required', 'exists:customers,id'],
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
                    ->ignore($invoice->id)
            ],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'sale_type'      => ['required', 'in:LOCAL,CENTRAL'],
            'tax_mode'       => ['required', 'in:simple,detailed'],
            'status'         => ['required', 'in:draft,unpaid,paid,partially_paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string'],

            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'          => ['required', 'string'],
            'items.*.domain_name'          => ['nullable', 'string', 'max:255'],
            'items.*.service_period_start' => ['nullable', 'date'],
            'items.*.service_period_end'   => ['nullable', 'date'],
            'items.*.billing_cycle'        => ['nullable', 'string', 'max:50'],
            'items.*.hsn_sac'              => ['nullable', 'string', 'max:20'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'          => ['required', 'string', 'max:20'],
            'items.*.rate'          => ['required', 'numeric', 'min:0'],
            'items.*.gst_percent'   => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            $isLocal = ($validated['sale_type'] === 'LOCAL');

            $invoice->update([
                'customer_id'     => $validated['customer_id'],
                'invoice_number'  => trim($validated['invoice_number']),
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'],
                'sale_type'       => $validated['sale_type'],
                'tax_mode'        => $validated['tax_mode'],
                'status'          => $validated['status'],
                'payment_method'  => $validated['payment_method'] ?? null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            // Rebuild items cleanly
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
                    'quantity'       => $qty,
                    'unit'           => $itemData['unit'] ?? 'Pcs',
                    'rate'           => $rate,
                    'gst_percent'    => $gstRate,
                    'taxable_amount' => $taxable,
                    'cgst_amount'    => $cgst,
                    'sgst_amount'    => $sgst,
                    'igst_amount'    => $igst,
                    'line_total'     => $lineTotal,
                ]);
            }

            $invoice->update([
                'taxable_amount' => $totalTaxable,
                'cgst_amount'    => $totalCgst,
                'sgst_amount'    => $totalSgst,
                'igst_amount'    => $totalIgst,
                'total_amount'   => $grandTotal,
            ]);

            ActivityLog::log('update', 'invoice', "Updated Invoice #{$invoice->invoice_number} details & items.", $invoice->id);
            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', "Invoice #{$invoice->invoice_number} updated successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->delete(); // Soft delete!

        ActivityLog::log('trash', 'invoice', "Moved invoice #{$num} to Trash.", $id);
        return back()->with('warning', "Invoice #{$num} moved to Trash. You can restore it anytime from the Trash tab.");
    }

    public function restore($id)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $invoice->restore();

        ActivityLog::log('restore', 'invoice', "Restored invoice #{$invoice->invoice_number} from Trash.", $id);
        return back()->with('success', "Invoice #{$invoice->invoice_number} restored successfully!");
    }

    public function forceDelete($id)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->items()->delete();
        $invoice->forceDelete();

        ActivityLog::log('force_delete', 'invoice', "Permanently deleted invoice #{$num}.");
        return back()->with('danger', "Invoice #{$num} permanently deleted.");
    }

    public function print($id)
    {
        $invoice = Invoice::withTrashed()->with(['customer', 'items', 'company'])->findOrFail($id);
        $company = $invoice->company;
        return view('invoices.print', compact('invoice', 'company'));
    }
}