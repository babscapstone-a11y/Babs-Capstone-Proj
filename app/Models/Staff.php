<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'first_name',
        'last_name',
        'birthday',
        'contact_no',
        'user_id',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * System-made starting password: last name (lowercase letters only) + birthday as MMDDYYYY,
     * e.g. "Dela Cruz", 15 Jan 2000 → "delacruz01152000". Null when no birthday is on file.
     */
    public function defaultPassword(): ?string
    {
        if (! $this->birthday) {
            return null;
        }

        $lastName = preg_replace('/[^a-z]/', '', strtolower(Str::ascii((string) $this->last_name)));

        return $lastName . $this->birthday->format('mdY');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
