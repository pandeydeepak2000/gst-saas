<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'gstin',
        'pan',
        'logo_path',
        'signature_path',
        'tax_mode', // 'simple' or 'detailed'
        'invoice_prefix',
        'invoice_start_number',
        'allow_manual_invoice_number',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_branch',
        'upi_id',
        'upi_name',
        'enable_upi_qr',
        'razorpay_key_id',
        'razorpay_key_secret',
        'enable_razorpay',
        'whatsapp_number',
        'whatsapp_template',
        'terms_and_conditions',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'invoice_template',
        'industry_type',
        'is_active',
    ];

    protected $casts = [
        'allow_manual_invoice_number' => 'boolean',
        'enable_upi_qr' => 'boolean',
        'enable_razorpay' => 'boolean',
        'is_active' => 'boolean',
        'invoice_start_number' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Generate the next sequential invoice number based on company settings
     */
    public function generateNextInvoiceNumber(): string
    {
        $prefix = $this->invoice_prefix ?: 'INV-';
        $startNumber = $this->invoice_start_number ?: 1;

        $latest = $this->invoices()->withTrashed()->latest('id')->first();
        if (!$latest) {
            return $prefix . str_pad((string)$startNumber, 4, '0', STR_PAD_LEFT);
        }

        $nextNum = $this->invoices()->withTrashed()->count() + $startNumber;
        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }
}