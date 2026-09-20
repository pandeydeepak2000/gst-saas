<?php
// setup_phase1.php

function writeFileSafe($path, $content) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $content);
    echo "Created: $path\n";
}

// 1. TenantScope
$tenantScope = <<<'PHP'
<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->company_id) {
            $builder->where($model->getTable() . '.company_id', auth()->user()->company_id);
        }
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/Scopes/TenantScope.php', $tenantScope);

// 2. Company Model
$companyModel = <<<'PHP'
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
        'terms_and_conditions',
        'is_active',
    ];

    protected $casts = [
        'allow_manual_invoice_number' => 'boolean',
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

        // Get count of existing invoices (including soft-deleted)
        $latest = $this->invoices()->withTrashed()->latest('id')->first();
        if (!$latest) {
            return $prefix . str_pad((string)$startNumber, 4, '0', STR_PAD_LEFT);
        }

        $nextNum = $this->invoices()->withTrashed()->count() + $startNumber;
        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/Company.php', $companyModel);

// 3. User Model
$userModel = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role', // 'super_admin', 'company_admin', 'staff', 'accountant'
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function canManageSettings(): bool
    {
        return in_array($this->role, ['super_admin', 'company_admin']);
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/User.php', $userModel);

// 4. Customer Model
$customerModel = <<<'PHP'
<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([TenantScope::class])]
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'company_name',
        'email',
        'phone',
        'billing_address',
        'city',
        'state',
        'pincode',
        'shipping_address',
        'gstin',
        'pan',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/Customer.php', $customerModel);

// 5. Invoice Model
$invoiceModel = <<<'PHP'
<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([TenantScope::class])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'created_by',
        'invoice_number',
        'invoice_date',
        'due_date',
        'sale_type', // LOCAL or CENTRAL
        'tax_mode',  // simple or detailed
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
        'status',
        'payment_method',
        'transaction_ref',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'taxable_amount' => 'float',
        'cgst_amount' => 'float',
        'sgst_amount' => 'float',
        'igst_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class);
    }

    public function getEffectiveTaxModeAttribute(): string
    {
        if (!empty($this->tax_mode)) {
            return $this->tax_mode;
        }
        return $this->company?->tax_mode ?: 'simple';
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/Invoice.php', $invoiceModel);

// 6. InvoiceItem Model
$itemModel = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'hsn_sac',
        'quantity',
        'unit',
        'rate',
        'gst_percent',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'rate' => 'float',
        'gst_percent' => 'float',
        'taxable_amount' => 'float',
        'cgst_amount' => 'float',
        'sgst_amount' => 'float',
        'igst_amount' => 'float',
        'line_total' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/InvoiceItem.php', $itemModel);

// 7. InvoiceTransaction Model
$transModel = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'gateway',
        'transaction_id',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/InvoiceTransaction.php', $transModel);

// 8. ActivityLog Model
$activityModel = <<<'PHP'
<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([TenantScope::class])]
class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'user_name',
        'role',
        'action',
        'module',
        'module_id',
        'description',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, string $module, string $description, ?int $moduleId = null): self
    {
        $user = auth()->user();
        return self::create([
            'company_id' => $user?->company_id,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?: 'System',
            'role' => $user?->role ?: 'system',
            'action' => $action,
            'module' => $module,
            'module_id' => $moduleId,
            'description' => $description,
        ]);
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Models/ActivityLog.php', $activityModel);

echo "Phase 1 Models completed successfully.\n";
