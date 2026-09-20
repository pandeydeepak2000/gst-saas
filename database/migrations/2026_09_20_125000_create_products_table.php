<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('hsn_sac', 20)->nullable();
            $table->string('unit', 20)->default('Pcs');
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(18.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'hsn_sac']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
