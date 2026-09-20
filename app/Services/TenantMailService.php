<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TenantMailService
{
    /**
     * Dynamically configure SMTP mailer using company settings
     */
    public static function configureCompanyMailer(?Company $company = null): bool
    {
        if (!$company || empty($company->mail_host)) {
            return false;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $company->mail_host);
        Config::set('mail.mailers.smtp.port', (int)($company->mail_port ?: 587));
        Config::set('mail.mailers.smtp.username', $company->mail_username);
        Config::set('mail.mailers.smtp.password', $company->mail_password);
        Config::set('mail.mailers.smtp.encryption', $company->mail_encryption === 'none' ? null : ($company->mail_encryption ?: 'tls'));

        $fromAddress = $company->mail_from_address ?: $company->email;
        $fromName = $company->mail_from_name ?: $company->name;

        if (!empty($fromAddress)) {
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
        }

        Mail::purge('smtp');
        return true;
    }

    /**
     * Send a test email using the company mail settings
     */
    public static function sendTestEmail(Company $company, string $targetEmail): array
    {
        if (!self::configureCompanyMailer($company)) {
            return [
                'success' => false,
                'message' => 'Please save valid SMTP Host, Username and Password before testing.'
            ];
        }

        try {
            $companyName = $company->name;
            Mail::raw("Greetings!\n\nThis is a confirmation test email sent from {$companyName}'s dedicated SMTP server on GST-SaaS Cloud Platform.\n\nAll outgoing invoice notifications and password resets will now be routed through your configured mail server.\n\nTime: " . now()->toDayDateTimeString(), function ($message) use ($targetEmail, $companyName) {
                $message->to($targetEmail)
                        ->subject("✅ [{$companyName}] SMTP Mail Server Test Successful");
            });

            return [
                'success' => true,
                'message' => "Test email successfully dispatched to {$targetEmail} via {$company->mail_host}!"
            ];
        } catch (\Exception $e) {
            Log::error("SMTP Test Error for company {$company->id}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "SMTP Connection Error: " . $e->getMessage()
            ];
        }
    }
}
