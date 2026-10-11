<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, HasPublicId, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
        'contact_no', 'address_id', 'profile_picture', 'status',
        'deactivation_reason', 'deactivated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
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

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class);
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
