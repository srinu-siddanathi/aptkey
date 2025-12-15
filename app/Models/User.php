<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
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
        ];
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is apartment manager
     */
    public function isApartmentManager(): bool
    {
        return $this->role === 'apartment_manager';
    }

    /**
     * Check if user is resident
     */
    public function isResident(): bool
    {
        return $this->role === 'resident';
    }

    /**
     * Get the tenant this user belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get units owned by this resident
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'resident_id');
    }

    /**
     * Get invoices for this resident
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'resident_id');
    }

    /**
     * Get complaints raised by this user
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'raised_by');
    }

    /**
     * Get complaints assigned to this user (for managers)
     */
    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    /**
     * Scope to filter by tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}

