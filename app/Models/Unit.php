<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'resident_id',
        'block',
        'unit_number',
        'type',
        'area_sqft',
        'monthly_maintenance',
        'is_occupied',
    ];

    protected $casts = [
        'area_sqft' => 'decimal:2',
        'monthly_maintenance' => 'decimal:2',
        'is_occupied' => 'boolean',
    ];

    /**
     * Get the tenant this unit belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the resident of this unit
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    /**
     * Get all invoices for this unit
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all complaints for this unit
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Get full unit identifier (Block + Unit Number)
     */
    public function getFullIdentifierAttribute(): string
    {
        return ($this->block ? $this->block . ' - ' : '') . $this->unit_number;
    }

    /**
     * Scope to filter by tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}

