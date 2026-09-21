<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;

class EmailOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
        'attempts'    => 'integer',
    ];

    /**
     * Generate a new 4-digit OTP with 60s cooldown, 10-min expiry, and cryptographically secure random_int.
     *
     * @throws Exception If cooldown is active
     */
    public static function generateFor(string $email, bool $ignoreCooldown = false): string
    {
        $normEmail = strtolower(trim($email));

        // Check 60-second cooldown against recent unverified OTP
        if (!$ignoreCooldown) {
            $recent = self::where('email', $normEmail)
                ->whereNull('verified_at')
                ->where('created_at', '>', Carbon::now()->subSeconds(60))
                ->latest('id')
                ->first();

            if ($recent) {
                $wait = 60 - Carbon::now()->diffInSeconds($recent->created_at);
                throw new Exception("Please wait {$wait} seconds before requesting a new verification code.");
            }
        }

        // Invalidate prior unverified OTPs for this email
        self::where('email', $normEmail)
            ->whereNull('verified_at')
            ->delete();

        // Cryptographically secure 4-digit numeric code
        $otp = (string) random_int(1000, 9999);

        self::create([
            'email'      => $normEmail,
            'otp'        => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts'   => 0,
        ]);

        return $otp;
    }

    /**
     * Verify OTP with maximum 5 attempts protection, expiry check, and single-use invalidation.
     */
    public static function verify(string $email, string $otp): bool
    {
        $normEmail = strtolower(trim($email));
        $cleanOtp = trim($otp);

        $record = self::where('email', $normEmail)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (!$record) {
            return false;
        }

        // Check if expired (> 10 minutes)
        if ($record->expires_at && $record->expires_at->isPast()) {
            $record->delete();
            return false;
        }

        // Increment failed attempt counter
        $record->increment('attempts');

        // Check if exceeded max 5 attempts -> revoke immediately
        if ($record->attempts > 5) {
            $record->delete();
            return false;
        }

        if (hash_equals($record->otp, $cleanOtp)) {
            $record->update(['verified_at' => Carbon::now()]);
            return true;
        }

        // If this 5th attempt was wrong, invalidate
        if ($record->attempts >= 5) {
            $record->delete();
        }

        return false;
    }
}