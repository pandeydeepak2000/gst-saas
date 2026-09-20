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