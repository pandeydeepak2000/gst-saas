<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('invoice_design_template')->default('modern')->after('invoice_template'); // 'modern' or 'classic'
            $table->boolean('show_bank_on_invoice')->default(true)->after('enable_upi_qr');
            $table->boolean('show_qr_on_invoice')->default(true)->after('show_bank_on_invoice');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['invoice_design_template', 'show_bank_on_invoice', 'show_qr_on_invoice']);
        });
    }
};