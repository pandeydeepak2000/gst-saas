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
            'permissions' => 'array',
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