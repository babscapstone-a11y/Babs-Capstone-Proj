<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A customer's profile. Their login (email, password, verification, account status) is a
 * `users` row with the "customer" role, linked through user_id; App\Auth\CustomerUserProvider
 * signs customers in against it. The storefront still works with this profile as the
 * logged-in customer, so auth('customer')->id() is the customer's id.
 */
class Customer extends Authenticatable
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, HasPublicId, Notifiable;

    protected $fillable = [
        'user_id', 'first_name', 'last_name',
        'contact_no', 'address_id', 'profile_picture',
        'deactivation_reason', 'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'deactivated_at' => 'datetime',
        ];
    }

    /** What a deactivated customer is told when they try to log in. */
    public function deactivationNotice(): string
    {
        $reason = trim((string) $this->deactivation_reason);
        if ($reason !== '' && ! preg_match('/[.!?]$/', $reason)) {
            $reason .= '.';
        }

        return 'Your account has been deactivated.'
            . ($reason !== '' ? ' Reason: ' . $reason : '')
            . ' Please contact the restaurant if you think this is a mistake.';
    }

    /* ── Relationships ───────────────────────────────────────── */

    /** The login account (users row, role "customer") */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /* ── Scopes ──────────────────────────────────────────────── */

    /** Customers whose login email is $email (stored in users) */
    public function scopeWithEmail(Builder $query, string $email): Builder
    {
        return $query->whereHas('user', fn ($q) => $q->where('email', $email));
    }

    /** Customers whose account status is $status ("active" / "inactive", stored in users) */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->whereHas('user', fn ($q) => $q->where('status', $status));
    }

    /* ── Login details (stored in users, read here as if on the customer) ── */

    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    public function getStatusAttribute(): ?string
    {
        return $this->user?->status;
    }

    public function getEmailVerifiedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->user?->email_verified_at;
    }

    public function getAuthPassword(): string
    {
        return (string) $this->user?->password;
    }

    public function getRememberToken(): ?string
    {
        return $this->user?->remember_token;
    }

    public function setRememberToken($value): void
    {
        $this->user?->setRememberToken($value);
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getInitialsAttribute(): string
    {
        $parts    = array_filter([$this->first_name, $this->last_name]);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper($part[0] ?? '');
        }
        return $initials ?: 'C';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEmailVerified(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : null;
    }
}
