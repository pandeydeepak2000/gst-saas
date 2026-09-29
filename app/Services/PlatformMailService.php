<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PlatformMailService
{
    /**
     * Check if platform SMTP host is configured
     */
    public static function isConfigured(): bool
    {
        return !empty(PlatformSetting::get('platform_mail_host', config('mail.mailers.smtp.host')));
    }

    /**
     * Dynamically configure SMTP mailer using Super Admin Platform Settings or production cPanel fallback
     */
    public static function configurePlatformMailer(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        $host = PlatformSetting::get('platform_mail_host');
        if (empty($host) || $host === '127.0.0.1' || $host === 'localhost') {
            $host = config('mail.mailers.smtp.host');
            if (empty($host) || $host === '127.0.0.1' || $host === 'localhost') {
                $host = 's4207.bom1.stableserver.net';
            }
        }

        $port = (int) (PlatformSetting::get('platform_mail_port') ?: (config('mail.mailers.smtp.port') ?: 465));
        $username = PlatformSetting::get('platform_mail_username') ?: (config('mail.mailers.smtp.username') ?: 'support@invocie.jixsite.com');
        $password = PlatformSetting::get('platform_mail_password') ?: (config('mail.mailers.smtp.password') ?: 'Mycard99@');
        $encryption = PlatformSetting::get('platform_mail_encryption') ?: (config('mail.mailers.smtp.encryption') ?: 'ssl');
        $fromAddress = PlatformSetting::get('platform_mail_from_address') ?: (config('mail.from.address') ?: 'support@invocie.jixsite.com');
        $fromName = PlatformSetting::get('platform_mail_from_name') ?: (config('mail.from.name') ?: 'GST-SaaS Platform Authority');

        $isSsl = ($encryption === 'ssl' || $port == 465);
        $scheme = $isSsl ? 'smtps' : 'smtp';

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.scheme', $scheme);
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port ?: ($isSsl ? 465 : 587));
        Config::set('mail.mailers.smtp.username', $username);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : ($encryption ?: ($isSsl ? 'ssl' : 'tls')));
        Config::set('mail.mailers.smtp.verify_peer', false);
        Config::set('mail.mailers.smtp.timeout', 15);

        if (!empty($fromAddress)) {
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
        }

        Mail::purge('smtp');
        return true;
    }

    /**
     * Retrieve all platform mail settings with fallbacks
     */
    public static function getSettings(): array
    {
        $host = PlatformSetting::get('platform_mail_host', 's4207.bom1.stableserver.net');
        return [
            'host'          => $host,
            'port'          => (int) PlatformSetting::get('platform_mail_port', 465),
            'username'      => PlatformSetting::get('platform_mail_username', 'support@invocie.jixsite.com'),
            'password'      => PlatformSetting::get('platform_mail_password', 'Mycard99@'),
            'encryption'    => PlatformSetting::get('platform_mail_encryption', 'ssl'),
            'from_address'  => PlatformSetting::get('platform_mail_from_address', 'support@invocie.jixsite.com'),
            'from_name'     => PlatformSetting::get('platform_mail_from_name', 'GST-SaaS Platform Authority'),
            'is_configured' => !empty($host),
        ];
    }

    /**
     * Send a raw email with mandatory RFC-compliant Message-ID, Date, and Sender headers
     */
    public static function sendRawMail(string $to, string $subject, string $body): void
    {
        self::configurePlatformMailer();

        $fromAddress = PlatformSetting::get('platform_mail_from_address') 
            ?: config('mail.from.address') 
            ?: 'support@invocie.jixsite.com';
        $fromName = PlatformSetting::get('platform_mail_from_name') 
            ?: config('mail.from.name') 
            ?: 'GST-SaaS Platform Authority';

        $domain = substr(strrchr($fromAddress, "@"), 1) ?: 'invocie.jixsite.com';
        $messageId = bin2hex(random_bytes(16)) . '@' . $domain;

        Mail::raw($body, function ($message) use ($to, $subject, $fromAddress, $fromName, $messageId) {
            $message->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName)
                    ->sender($fromAddress, $fromName)
                    ->replyTo($fromAddress, $fromName)
                    ->returnPath($fromAddress);

            $headers = $message->getSymfonyMessage()->getHeaders();

            // Explicitly force mandatory RFC headers expected by cPanel outbound relay filters
            $headers->remove('Date');
            $headers->addDateHeader('Date', new \DateTimeImmutable());

            $headers->remove('Message-ID');
            $headers->addIdHeader('Message-ID', $messageId);

            $headers->remove('X-Mailer');
            $headers->addTextHeader('X-Mailer', 'GST-SaaS Platform Authority');
        });
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
            $host = PlatformSetting::get('platform_mail_host', 's4207.bom1.stableserver.net');
            $fromName = PlatformSetting::get('platform_mail_from_name', 'GST-SaaS Master Platform');

            $subject = "✅ [{$fromName}] Platform Auth SMTP Diagnostic Test Successful";
            $body = "Greetings!\n\nThis is an official verification test email sent from the GST-SaaS Super Admin Platform Mail Server ({$host}).\n\nAll tenant onboarding OTPs, 2FA login verification codes, and password resets will now route through this server.\n\nDispatched: " . now()->toDayDateTimeString();

            self::sendRawMail($targetEmail, $subject, $body);

            return [
                'success' => true,
                'message' => "Diagnostic test email successfully dispatched to {$targetEmail} via {$host}!"
            ];
        } catch (\Throwable $e) {
            Log::error("Platform Mail Test Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Platform SMTP Connection Error: " . $e->getMessage()
            ];
        }
    }
}
