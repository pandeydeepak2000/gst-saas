<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use Illuminate\Http\Request;

class PublicInvoiceController extends Controller
{
    /**
     * Show client-facing public invoice portal via secure UUID token
     */
    public function show(string $uuid)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->with(['company', 'customer', 'items', 'transactions'])
            ->where('public_uuid', $uuid)
            ->firstOrFail();

        $company = $invoice->company;

        // Build UPI Payment Payload if company has UPI enabled
        $upiUrl = null;
        if ($company->enable_upi_qr && !empty($company->upi_id) && $invoice->balance_amount > 0) {
            $pa = rawurlencode($company->upi_id);
            $pn = rawurlencode($company->upi_name ?: $company->name);
            $am = number_format($invoice->balance_amount, 2, '.', '');
            $tr = rawurlencode($invoice->invoice_number);
            $tn = rawurlencode('Invoice ' . $invoice->invoice_number);
            $upiUrl = "upi://pay?pa={$pa}&pn={$pn}&am={$am}&tr={$tr}&tn={$tn}&cu=INR";
        }

        return view('invoices.public_view', compact('invoice', 'company', 'upiUrl'));
    }

    /**
     * Handle Razorpay Online Payment Callback for Public Invoice
     */
    public function razorpayCallback(Request $request, string $uuid)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->with('company')
            ->where('public_uuid', $uuid)
            ->firstOrFail();

        $company = $invoice->company;

        $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
        ]);

        $paymentId = $request->razorpay_payment_id;
        $amountPaid = (float) ($request->amount ?? $invoice->balance_amount);

        // Optional HMAC signature check if secret is configured and signature passed
        if (!empty($company->razorpay_key_secret) && $request->has('razorpay_signature') && $request->has('razorpay_order_id')) {
            $expectedSignature = hash_hmac(
                'sha256',
                $request->razorpay_order_id . '|' . $paymentId,
                $company->razorpay_key_secret
            );
            if ($expectedSignature !== $request->razorpay_signature) {
                return response()->json(['success' => false, 'message' => 'Payment verification failed.'], 400);
            }
        }

        // Record the transaction
        InvoiceTransaction::create([
            'company_id'     => $company->id,
            'invoice_id'     => $invoice->id,
            'gateway'        => 'Razorpay',
            'payment_method' => 'razorpay',
            'transaction_id' => $paymentId,
            'amount'         => $amountPaid,
            'paid_at'        => now(),
            'notes'          => 'Online payment received via Razorpay Gateway',
        ]);

        $invoice->recalculatePaymentStatus();

        ActivityLog::log(
            'create',
            'payment',
            "Online Razorpay payment of ₹" . number_format($amountPaid, 2) . " received for Invoice #" . $invoice->invoice_number . " (Payment ID: {$paymentId})"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully! Thank you.',
                'invoice_status' => $invoice->status,
                'balance_amount' => $invoice->balance_amount
            ]);
        }

        return redirect()->route('public.invoice.show', $uuid)->with('success', 'Payment of ₹' . number_format($amountPaid, 2) . ' received successfully via Razorpay! Invoice updated.');
    }
}