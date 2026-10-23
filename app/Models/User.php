<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPublicId, Notifiable, SoftDeletes;

    protected $fillable = [
        'email',
        'username',
        'password',
        'must_change_password',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'must_change_password' => 'boolean',
            'deleted_at'        => 'datetime',
        ];
    }

    /* ── Relationships ───────────────────────────────────────── */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    /** Profile of a customer account (role "customer") */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function passwordResetRequests(): HasMany
    {
        return $this->hasMany(StaffPasswordResetRequest::class, 'user_id');
    }

    public function requestedPasswordResets(): HasMany
    {
        return $this->hasMany(StaffPasswordResetRequest::class, 'requested_by');
    }

    /* ── Scopes ──────────────────────────────────────────────── */

    /** Staff logins (admin, cashier, kitchen staff, table server) — every role except customer */
    public function scopeStaffAccounts(Builder $query): Builder
    {
        return $query->whereHas('role', fn ($q) => $q->where('role_name', '!=', Role::CUSTOMER));
    }

    /** Customer logins (role "customer") */
    public function scopeCustomerAccounts(Builder $query): Builder
    {
        return $query->whereHas('role', fn ($q) => $q->where('role_name', Role::CUSTOMER));
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    public function isCustomer(): bool
    {
        return $this->role?->role_name === Role::CUSTOMER;
    }

    public function isAdmin(): bool
    {
        return $this->role?->role_name === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role?->role_name === 'cashier';
    }

    public function isKitchenStaff(): bool
    {
        return $this->role?->role_name === 'kitchen_staff';
    }

    public function isTableServer(): bool
    {
        return $this->role?->role_name === 'table_server';
    }

    /**
     * Puts a staff account back on its default password (last name + birthday) and has the next
     * log-in ask for a new one. Returns the default password, or null when no birthday is on file.
     */
    public function resetToDefaultPassword(): ?string
    {
        $default = $this->staff?->defaultPassword();

        if ($default === null) {
            return null;
        }

        $this->forceFill(['password' => $default, 'must_change_password' => true])->save();

        return $default;
    }

    /** Where a staff member lands after logging in (or after setting their first password) */
    public function homeRoute(): string
    {
        return match (true) {
            $this->isAdmin()       => route('dashboard'),
            $this->isCashier()     => route('cashier.billing'),
            $this->isKitchenStaff() => route('kitchen.index'),
            $this->isTableServer() => route('table-server.index'),
            default                => route('profile.edit'),
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getNameAttribute(): string
    {
        return $this->staff?->full_name ?? '';
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper($word[0] ?? '');
        }
        return $initials ?: 'U';
    }
}
