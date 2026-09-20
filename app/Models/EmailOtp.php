<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
    ];

    public static function generateFor(string $email): string
    {
        // Invalidate prior unverified OTPs for this email
        self::where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        $otp = (string) mt_rand(1000, 9999);

        self::create([
            'email'      => strtolower(trim($email)),
            'otp'        => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        return $otp;
    }

    public static function verify(string $email, string $otp): bool
    {
        $record = self::where('email', strtolower(trim($email)))
            ->where('otp', trim($otp))
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest('id')
            ->first();

        if ($record) {
            $record->update(['verified_at' => Carbon::now()]);
            return true;
        }

        return false;
    }
}