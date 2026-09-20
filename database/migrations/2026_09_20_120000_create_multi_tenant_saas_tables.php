<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. COMPANIES (TENANTS)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->default('Bihar');
            $table->string('pincode', 10)->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('pan', 15)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('signature_path')->nullable();

            // Tax display toggle: 'simple' (e.g. GST 18%) vs 'detailed' (CGST/SGST/IGST split)
            $table->enum('tax_mode', ['simple', 'detailed'])->default('simple');

            // Customizable Invoice Numbering
            $table->string('invoice_prefix')->default('INV-');
            $table->unsignedInteger('invoice_start_number')->default(1);
            $table->boolean('allow_manual_invoice_number')->default(true);

            // Bank details for invoice footer & payment QR
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('upi_id')->nullable();

            $table->text('terms_and_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. USERS TABLE MULTI-TENANCY & RBAC
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            $table->enum('role', ['super_admin', 'company_admin', 'staff', 'accountant'])->default('company_admin')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
        });

        // 3. CUSTOMERS
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->default('Bihar');
            $table->string('pincode', 10)->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('pan', 15)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'phone']);
        });

        // 4. INVOICES (WITH SOFT DELETE / TRASH & MANUALLY EDITABLE NUMBER)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('invoice_number'); // Unique per company, freely editable
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            $table->string('sale_type', 15)->default('LOCAL'); // LOCAL (CGST+SGST) or CENTRAL (IGST)
            $table->enum('tax_mode', ['simple', 'detailed'])->default('simple');

            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('igst_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', ['draft', 'unpaid', 'paid', 'partially_paid', 'cancelled'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->text('notes')->nullable();

            $table->softDeletes(); // For Trash Bin & Recovery!
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'invoice_date']);
            $table->index(['company_id', 'status']);
        });

        // 5. INVOICE ITEMS
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description');
            $table->string('hsn_sac', 20)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 20)->default('Pcs');
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(18);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('igst_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });

        // 6. INVOICE TRANSACTIONS
        Schema::create('invoice_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('gateway')->default('UPI / Bank Transfer');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->dateTime('paid_at');
            $table->timestamps();

            $table->index(['invoice_id', 'paid_at']);
        });

        // 7. ACTIVITY LOGS (AUDIT TRAIL)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->default('User');
            $table->string('role', 30)->nullable();
            $table->string('action', 50);
            $table->string('module', 50);
            $table->unsignedBigInteger('module_id')->nullable();
            $table->text('description');
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('invoice_transactions');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('customers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'role', 'phone', 'is_active']);
        });
        Schema::dropIfExists('companies');
    }
};
