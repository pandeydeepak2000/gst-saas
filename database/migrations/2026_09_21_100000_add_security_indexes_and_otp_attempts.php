<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add attempts to email_otps if not present
        if (Schema::hasTable('email_otps') && !Schema::hasColumn('email_otps', 'attempts')) {
            Schema::table('email_otps', function (Blueprint $table) {
                $table->unsignedInteger('attempts')->default(0)->after('verified_at');
            });
        }

        // 2. Add performance & tenant isolation indexes to invoices
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['company_id', 'status'], 'idx_invoices_company_status');
                $table->index(['company_id', 'invoice_date'], 'idx_invoices_company_date');
            });
        }

        // 3. Add index to customers
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index(['company_id', 'name'], 'idx_customers_company_name');
            });
        }

        // 4. Add index to products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['company_id', 'name'], 'idx_products_company_name');
            });
        }

        // 5. Add index to invoice_transactions
        if (Schema::hasTable('invoice_transactions')) {
            Schema::table('invoice_transactions', function (Blueprint $table) {
                $table->index(['company_id', 'paid_at'], 'idx_transactions_company_paid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('email_otps') && Schema::hasColumn('email_otps', 'attempts')) {
            Schema::table('email_otps', function (Blueprint $table) {
                $table->dropColumn('attempts');
            });
        }
    }
};