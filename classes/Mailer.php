<?php
/**
 * Mailer.php — PHPMailer wrapper for sending transactional emails
 */
class Mailer
{
    public static function send(
        string $to,
        string $toName,
        string $subject,
        string $htmlBody,
        string $plainBody = ''
    ): bool {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = Config::Get('MAIL_HOST', 'localhost');
            $mail->SMTPAuth = !empty(Config::Get('MAIL_USER'));
            $mail->Username = Config::Get('MAIL_USER', '');
            $mail->Password = Config::Get('MAIL_PASS', '');
            $mail->SMTPSecure = Config::Get('MAIL_ENCRYPTION', 'tls') === 'ssl'
                ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) Config::Get('MAIL_PORT', 587);

            // From
            $mail->setFrom(
                Config::Get('MAIL_FROM', 'noreply@subtrack.app'),
                Config::Get('MAIL_FROM_NAME', 'SubTrack')
            );

            // To
            $mail->addAddress($to, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody ?: strip_tags($htmlBody);
            $mail->CharSet = 'UTF-8';

            $mail->send();
            Logger::info("Email sent to {$to} — Subject: {$subject}");
            return true;
        } catch (PHPMailer\PHPMailer\Exception $e) {
            Logger::error("Mailer failed to {$to}: " . $e->getMessage());
            return false;
        }
    }

    public static function sendVerification(string $to, string $name, string $token): bool
    {
        $url = UrlHelper::base("auth/verify-email.php?token={$token}");
        $subject = 'Verify your SubTrack email address';
        $html = self::wrap($name, "
            <p>Thanks for registering with SubTrack! Please verify your email address by clicking the button below.</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='{$url}' style='background:#1D4ED8;color:white;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block'>Verify Email Address</a>
            </p>
            <p style='color:#6b7280;font-size:14px'>This link expires in 24 hours. If you didn't create an account, you can safely ignore this email.</p>
        ");
        return self::send($to, $name, $subject, $html);
    }

    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $url = UrlHelper::base("auth/reset-password.php?token={$token}");
        $subject = 'Reset your SubTrack password';
        $html = self::wrap($name, "
            <p>We received a request to reset your SubTrack password. Click the button below to set a new password.</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='{$url}' style='background:#1D4ED8;color:white;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block'>Reset Password</a>
            </p>
            <p style='color:#6b7280;font-size:14px'>This link expires in 48 hours. If you didn't request a password reset, you can safely ignore this email.</p>
        ");
        return self::send($to, $name, $subject, $html);
    }

    public static function sendReminder(string $to, string $name, string $subName, float $amount, string $currency, string $billingDate): bool
    {
        $amountFmt = CurrencyHelper::format($amount, $currency);
        $days = DateHelper::daysUntil($billingDate);
        $dateStr = DateHelper::formatUK($billingDate);
        $daysLabel = $days === 0 ? 'today' : ($days === 1 ? 'tomorrow' : "in {$days} days");
        $subject = "{$subName} renews {$daysLabel} — {$amountFmt}";
        $html = self::wrap($name, "
            <p>This is a reminder that your <strong>{$subName}</strong> subscription is due to renew <strong>{$daysLabel}</strong>.</p>
            <table style='width:100%;border-collapse:collapse;margin:24px 0'>
                <tr><td style='padding:12px;background:#f9fafb;font-weight:600'>Subscription</td><td style='padding:12px;background:#f9fafb'>{$subName}</td></tr>
                <tr><td style='padding:12px;font-weight:600'>Amount</td><td style='padding:12px'>{$amountFmt}</td></tr>
                <tr><td style='padding:12px;background:#f9fafb;font-weight:600'>Renewal Date</td><td style='padding:12px;background:#f9fafb'>{$dateStr}</td></tr>
            </table>
            <p style='text-align:center;margin:24px 0'>
                <a href='" . UrlHelper::base('dashboard/subscriptions.php') . "' style='background:#1D4ED8;color:white;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block'>View in SubTrack</a>
            </p>
        ");
        return self::send($to, $name, $subject, $html);
    }

    public static function sendWelcomeGoogle(string $to, string $name): bool
    {
        $subject = 'Welcome to SubTrack!';
        $html = self::wrap($name, "
            <p>Welcome to SubTrack! Your account has been created using Google Sign-In.</p>
            <p>Start tracking your subscriptions to see exactly how much you spend each month.</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='" . UrlHelper::base('dashboard/add-subscription.php') . "' style='background:#1D4ED8;color:white;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block'>Add Your First Subscription</a>
            </p>
        ");
        return self::send($to, $name, $subject, $html);
    }

    private static function wrap(string $name, string $content): string
    {
        $appName = 'SubTrack';
        $appUrl = UrlHelper::base();
        $year = date('Y');
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif">
            <div style="max-width:600px;margin:40px auto;background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,.07)">
                <div style="background:linear-gradient(135deg,#1D4ED8,#6366F1);padding:32px;text-align:center">
                    <h1 style="color:white;margin:0;font-size:24px;font-weight:700">{$appName}</h1>
                    <p style="color:rgba(255,255,255,.8);margin:8px 0 0;font-size:14px">Subscription Tracker</p>
                </div>
                <div style="padding:40px">
                    <p style="color:#374151;margin:0 0 16px">Hi {$name},</p>
                    {$content}
                </div>
                <div style="background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #e5e7eb">
                    <p style="color:#9ca3af;font-size:12px;margin:0">© {$year} {$appName} · <a href="{$appUrl}/privacy-policy.php" style="color:#6b7280">Privacy Policy</a> · <a href="{$appUrl}/terms.php" style="color:#6b7280">Terms</a></p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
