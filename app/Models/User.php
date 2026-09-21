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
        'permissions',
        'phone',
        'is_active',
        'is_2fa_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'last_seen_at',
        'last_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'is_active'             => 'boolean',
            'is_2fa_enabled'        => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'last_seen_at'          => 'datetime',
            'permissions'           => 'array',
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

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    public function canManageSettings(): bool
    {
        return in_array($this->role, ['super_admin', 'company_admin']);
    }

    public function hasPermission(string $module): bool
    {
        if ($this->isSuperAdmin() || $this->isCompanyAdmin()) {
            return true;
        }

        if (!$this->is_active) {
            return false;
        }

        $perms = $this->permissions ?? [];
        return in_array($module, $perms);
    }
}
