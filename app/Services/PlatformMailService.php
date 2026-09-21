<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PlatformMailService
{
    /**
     * Dynamically configure SMTP mailer using Super Admin Platform Settings
     */
    public static function configurePlatformMailer(): bool
    {
        $host = PlatformSetting::get('platform_mail_host');

        if (empty($host)) {
            return false;
        }

        $port = (int) PlatformSetting::get('platform_mail_port', 587);
        $username = PlatformSetting::get('platform_mail_username');
        $password = PlatformSetting::get('platform_mail_password');
        $encryption = PlatformSetting::get('platform_mail_encryption', 'tls');
        $fromAddress = PlatformSetting::get('platform_mail_from_address');
        $fromName = PlatformSetting::get('platform_mail_from_name', 'GST-SaaS Platform');

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port ?: 587);
        Config::set('mail.mailers.smtp.username', $username);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : ($encryption ?: 'tls'));

        if (!empty($fromAddress)) {
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
        }

        Mail::purge('smtp');
        return true;
    }

    /**
     * Retrieve all platform mail settings
     */
    public static function getSettings(): array
    {
        $host = PlatformSetting::get('platform_mail_host');
        return [
            'host'          => $host,
            'port'          => PlatformSetting::get('platform_mail_port', 587),
            'username'      => PlatformSetting::get('platform_mail_username'),
            'password'      => PlatformSetting::get('platform_mail_password'),
            'encryption'    => PlatformSetting::get('platform_mail_encryption', 'tls'),
            'from_address'  => PlatformSetting::get('platform_mail_from_address'),
            'from_name'     => PlatformSetting::get('platform_mail_from_name', 'GST-SaaS Platform Authority'),
            'is_configured' => !empty($host),
        ];
    }

    /**
     * Send a diagnostic test email via Super Admin Platform SMTP
     */
    public static function sendTestEmail(string $targetEmail): array
    {
        if (!self::configurePlatformMailer()) {
            return [
                'success' => false,
                'message' => 'Please save a valid Platform SMTP Host and Username before testing.'
            ];
        }

        try {
            $host = PlatformSetting::get('platform_mail_host');
            $fromName = PlatformSetting::get('platform_mail_from_name', 'GST-SaaS Master Platform');

            Mail::raw("Greetings!\n\nThis is an official verification test email sent from the GST-SaaS Super Admin Platform Mail Server ({$host}).\n\nAll tenant onboarding OTPs, 2FA login verification codes, and password resets will now route through this server.\n\nDispatched: " . now()->toDayDateTimeString(), function ($message) use ($targetEmail, $fromName) {
                $message->to($targetEmail)
                        ->subject("? [{$fromName}] Platform Auth SMTP Diagnostic Test Successful");
            });

            return [
                'success' => true,
                'message' => "Diagnostic test email successfully dispatched to {$targetEmail} via {$host}!"
            ];
        } catch (\Exception $e) {
            Log::error("Platform Mail Test Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Platform SMTP Connection Error: " . $e->getMessage()
            ];
        }
    }
}
