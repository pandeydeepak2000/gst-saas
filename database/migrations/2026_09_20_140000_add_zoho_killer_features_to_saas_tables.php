<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('upi_name')->nullable()->after('upi_id');
            $table->boolean('enable_upi_qr')->default(false)->after('upi_name');
            $table->string('razorpay_key_id')->nullable()->after('enable_upi_qr');
            $table->string('razorpay_key_secret')->nullable()->after('razorpay_key_id');
            $table->boolean('enable_razorpay')->default(false)->after('razorpay_key_secret');
            $table->string('whatsapp_number')->nullable()->after('enable_razorpay');
            $table->text('whatsapp_template')->nullable()->after('whatsapp_number');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('public_uuid', 64)->nullable()->unique()->after('invoice_number');
            $table->string('type')->default('tax_invoice')->after('public_uuid');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('balance_amount', 12, 2)->default(0)->after('paid_amount');
        });

        Schema::table('invoice_transactions', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            $table->string('payment_method')->nullable()->after('gateway');
            $table->text('notes')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_transactions', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'payment_method', 'notes']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['public_uuid', 'type', 'paid_amount', 'balance_amount']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'upi_name', 'enable_upi_qr',
                'razorpay_key_id', 'razorpay_key_secret', 'enable_razorpay',
                'whatsapp_number', 'whatsapp_template'
            ]);
        });
    }
};