<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Record a manual payment (Cash, Cheque, NEFT/RTGS, UPI) or offline entry
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $user = auth()->user();
        if ($invoice->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized invoice access: This invoice does not belong to your company.');
        }

        $maxAmount = $invoice->balance_amount > 0 ? $invoice->balance_amount : $invoice->total_amount;

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:' . ($maxAmount + 0.01)],
            'payment_method' => ['required', 'string', 'in:cash,upi,neft,cheque,card,razorpay'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'paid_at'        => ['required', 'date'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $transaction = InvoiceTransaction::create([
            'company_id'     => $invoice->company_id,
            'invoice_id'     => $invoice->id,
            'gateway'        => strtoupper($validated['payment_method']) . ' Manual',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['reference_no'] ?? ('MANUAL-' . strtoupper(uniqid())),
            'amount'         => (float) $validated['amount'],
            'paid_at'        => $validated['paid_at'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        $invoice->recalculatePaymentStatus();

        ActivityLog::log(
            'create',
            'payment',
            "Recorded " . strtoupper($validated['payment_method']) . " payment of ₹" . number_format($validated['amount'], 2) . " for Invoice #" . $invoice->invoice_number
        );

        return back()->with('success', 'Payment of ₹' . number_format($validated['amount'], 2) . ' recorded successfully! Status: ' . strtoupper(str_replace('_', ' ', $invoice->status)));
    }

    /**
     * Remove / cancel a payment transaction
     */
    public function destroy(InvoiceTransaction $transaction)
    {
        $user = auth()->user();
        if ($transaction->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized transaction access: This record does not belong to your company.');
        }

        $invoice = $transaction->invoice;
        $amount = $transaction->amount;
        $transaction->delete();

        if ($invoice) {
            $invoice->recalculatePaymentStatus();
        }

        ActivityLog::log('delete', 'payment', "Cancelled payment transaction of ₹" . number_format($amount, 2) . ($invoice ? " on Invoice #" . $invoice->invoice_number : ""));

        return back()->with('success', 'Payment transaction deleted and invoice balance recalculated.');
    }

    /**
     * Printable Payment Receipt Voucher
     */
    public function receipt(InvoiceTransaction $transaction)
    {
        $user = auth()->user();
        if ($transaction->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized receipt access: This voucher does not belong to your company.');
        }

        $invoice = $transaction->invoice()->with(['customer', 'company'])->firstOrFail();
        return view('payments.receipt', compact('transaction', 'invoice'));
    }
}