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
        'invoice_design_template',
        'brand_theme',
        'show_bank_on_invoice',
        'show_qr_on_invoice',
        'is_active',
        'approval_status',
        'trial_ends_at',
    ];

    protected $casts = [
        'allow_manual_invoice_number' => 'boolean',
        'enable_upi_qr' => 'boolean',
        'enable_razorpay' => 'boolean',
        'show_bank_on_invoice' => 'boolean',
        'show_qr_on_invoice' => 'boolean',
        'is_active' => 'boolean',
        'invoice_start_number' => 'integer',
        'trial_ends_at' => 'datetime',
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
    public function emailChangeRequests(): HasMany
    {
        return $this->hasMany(EmailChangeRequest::class);
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved' && $this->is_active;
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function getThemeConfigAttribute(): array
    {
        $theme = $this->brand_theme ?: 'violet';
        if ($theme === 'emerald') {
            return [
                'key'            => 'emerald',
                'name'           => 'Emerald Merchant',
                'primary'        => '#059669',
                'sidebar_active' => 'bg-emerald-600 text-white font-semibold shadow-sm',
                'sidebar_hover'  => 'hover:bg-emerald-500/10 hover:text-emerald-300',
                'button_primary' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20',
                'hero_gradient'  => 'from-slate-950 via-slate-900 to-emerald-950',
                'accent_badge'   => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                'kpi_icon_bg'    => 'bg-emerald-50 text-emerald-600',
                'ring_focus'     => 'focus:ring-emerald-500',
            ];
        }

        if ($theme === 'amber') {
            return [
                'key'            => 'amber',
                'name'           => 'Imperial Amber',
                'primary'        => '#d97706',
                'sidebar_active' => 'bg-amber-600 text-white font-semibold shadow-sm',
                'sidebar_hover'  => 'hover:bg-amber-500/10 hover:text-amber-300',
                'button_primary' => 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/20',
                'hero_gradient'  => 'from-slate-950 via-slate-900 to-amber-950',
                'accent_badge'   => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                'kpi_icon_bg'    => 'bg-amber-50 text-amber-600',
                'ring_focus'     => 'focus:ring-amber-500',
            ];
        }

        if ($theme === 'rose') {
            return [
                'key'            => 'rose',
                'name'           => 'Titan Crimson',
                'primary'        => '#e11d48',
                'sidebar_active' => 'bg-rose-600 text-white font-semibold shadow-sm',
                'sidebar_hover'  => 'hover:bg-rose-500/10 hover:text-rose-300',
                'button_primary' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-600/20',
                'hero_gradient'  => 'from-slate-950 via-slate-900 to-rose-950',
                'accent_badge'   => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                'kpi_icon_bg'    => 'bg-rose-50 text-rose-600',
                'ring_focus'     => 'focus:ring-rose-500',
            ];
        }

        // Default: Amethyst / Electric Violet SaaS (Never generic blue!)
        return [
            'key'            => 'violet',
            'name'           => 'Electric Amethyst',
            'primary'        => '#7c3aed',
            'sidebar_active' => 'bg-violet-600 text-white font-semibold shadow-sm',
            'sidebar_hover'  => 'hover:bg-violet-500/10 hover:text-violet-300',
            'button_primary' => 'bg-violet-600 hover:bg-violet-700 text-white shadow-violet-600/20',
            'hero_gradient'  => 'from-slate-950 via-slate-900 to-purple-950',
            'accent_badge'   => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
            'kpi_icon_bg'    => 'bg-purple-50 text-purple-600',
            'ring_focus'     => 'focus:ring-violet-500',
        ];
    }
}