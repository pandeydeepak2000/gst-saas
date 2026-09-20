<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Per-Company Dedicated SMTP Mail Settings & Invoice Template Mode
        Schema::table('companies', function (Blueprint $table) {
            $table->string('mail_host')->nullable()->after('upi_id');
            $table->unsignedInteger('mail_port')->default(587)->after('mail_host');
            $table->string('mail_username')->nullable()->after('mail_port');
            $table->string('mail_password')->nullable()->after('mail_username');
            $table->string('mail_encryption', 10)->default('tls')->after('mail_password');
            $table->string('mail_from_address')->nullable()->after('mail_encryption');
            $table->string('mail_from_name')->nullable()->after('mail_from_address');

            // Template preset: 'standard' or 'hosting_domain' (with service periods & domain tags)
            $table->string('invoice_template', 30)->default('standard')->after('mail_from_name');
            $table->string('industry_type', 30)->default('general')->after('invoice_template');
        });

        // 2. Hosting & Domain specific metadata for invoice line items
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('domain_name')->nullable()->after('description');
            $table->date('service_period_start')->nullable()->after('domain_name');
            $table->date('service_period_end')->nullable()->after('service_period_start');
            $table->string('billing_cycle', 30)->nullable()->after('service_period_end'); // e.g. 1 Year, 1 Month, 3 Years
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['domain_name', 'service_period_start', 'service_period_end', 'billing_cycle']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'mail_host', 'mail_port', 'mail_username', 'mail_password',
                'mail_encryption', 'mail_from_address', 'mail_from_name',
                'invoice_template', 'industry_type'
            ]);
        });
    }
};
