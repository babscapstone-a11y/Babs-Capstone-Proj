<?php

namespace App\Models;

use Database\Factories\UserFactory;
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
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
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

    /* ── Helpers ─────────────────────────────────────────────── */

    public function isAdmin(): bool
    {
        return $this->role?->role_name === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role?->role_name === 'customer';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRoleDisplayAttribute(): string
    {
        return $this->role?->display_name ?? ucfirst(str_replace('_', ' ', $this->role?->role_name ?? 'Unknown'));
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
