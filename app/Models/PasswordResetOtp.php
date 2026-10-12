<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PasswordResetOtp extends Model
{
    protected $table = 'password_reset_otps';

    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'otp',
        'attempts',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /** Minutes a self-requested code (customer or admin) stays valid. */
    const EXPIRES_MINUTES = 10;

    /**
     * Minutes a code sent on admin approval stays valid. Longer, because the staff member
     * isn't waiting on the page when it arrives.
     */
    const APPROVED_EXPIRES_MINUTES = 60;

    /**
     * Create (or replace) the reset code for an email address and return the plain
     * 6-digit code to send. Only its hash is stored.
     */
    public static function issue(string $email, int $minutes = self::EXPIRES_MINUTES): string
    {
        $otp = (string) random_int(100000, 999999);

        static::updateOrCreate(
            ['email' => $email],
            [
                'otp' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes($minutes),
                'created_at' => now(),
            ]
        );

        return $otp;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
