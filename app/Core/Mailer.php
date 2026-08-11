<?php

namespace App\Core;

/**
 * Sends branded HTML emails via PHP's mail(). Wraps whatever content HTML
 * is passed in with a shared, email-client-safe (table-based, inline CSS)
 * King's Cut Saloon shell.
 *
 * Like Share::sendEmail(), this gracefully reports failure rather than
 * throwing — most local dev environments (including a stock XAMPP install)
 * have no outgoing mail transport configured. See the README for enabling
 * real delivery.
 */
class Mailer
{
    private const BRAND_INK = '#1B1410';
    private const BRAND_BRASS = '#C89B3C';
    private const BRAND_PARCHMENT = '#F3E8D6';
    private const BRAND_DIM = '#8A7A5C';

    /** Wrap raw body HTML in the shared branded email shell. */
    public static function shell(string $preheader, string $bodyHtml): string
    {
        $ink = self::BRAND_INK;
        $brass = self::BRAND_BRASS;
        $parchment = self::BRAND_PARCHMENT;
        $dim = self::BRAND_DIM;
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>King's Cut Saloon</title>
</head>
<body style="margin:0; padding:0; background:#EFE7D8; font-family:Georgia, 'Times New Roman', serif;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{$preheader}</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EFE7D8; padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #E3D9C4;">

          <tr>
            <td style="background:{$ink}; padding:26px 32px;">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="font-size:20px; font-weight:bold; color:{$parchment}; letter-spacing:0.5px;">
                    KING&rsquo;S CUT
                  </td>
                </tr>
                <tr>
                  <td style="font-size:10px; letter-spacing:2px; color:{$brass}; text-transform:uppercase; padding-top:2px; font-family:Arial, sans-serif;">
                    Saloon &amp; Membership
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:32px;">
              {$bodyHtml}
            </td>
          </tr>

          <tr>
            <td style="padding:20px 32px; background:#F7F1E6; border-top:1px solid #E3D9C4;">
              <p style="margin:0; font-family:Arial, sans-serif; font-size:11px; color:{$dim}; line-height:1.6;">
                King&rsquo;s Cut Saloon &middot; 14 Ring Road, Benin City, Edo State<br>
                &copy; {$year} King&rsquo;s Cut Saloon. This is an automated message — please don&rsquo;t reply directly.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    public static function send(string $toEmail, string $subject, string $bodyHtml): bool
    {
        $headers = "From: King's Cut Saloon <no-reply@kingscutsaloon.com>\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n";

        return @mail($toEmail, $subject, $bodyHtml, $headers);
    }

    // ---- Specific transactional emails ----

    public static function sendOtp(string $toEmail, string $firstName, string $otpCode): bool
    {
        $body = self::otpBody(e($firstName), $otpCode);
        return self::send($toEmail, 'Your King\'s Cut Saloon password reset code', self::shell('Your one-time code is ' . $otpCode, $body));
    }

    public static function sendWelcome(string $toEmail, string $firstName): bool
    {
        $body = self::welcomeBody(e($firstName));
        return self::send($toEmail, 'Welcome to King\'s Cut Saloon', self::shell('Your account is ready.', $body));
    }

    public static function sendPaymentReceipt(string $toEmail, string $firstName, string $description, float $amount, string $method, string $dateLabel): bool
    {
        $body = self::paymentBody(e($firstName), e($description), $amount, $method, e($dateLabel));
        return self::send($toEmail, 'Payment received — ' . $description, self::shell('Receipt for ' . money($amount), $body));
    }

    public static function sendReservationReceived(string $toEmail, string $firstName, string $sessionLabel, string $dateLabel, int $numberOfPeople, float $estimatedTotal): bool
    {
        $body = self::reservationBody(e($firstName), e($sessionLabel), e($dateLabel), $numberOfPeople, $estimatedTotal);
        return self::send($toEmail, 'We received your reservation request', self::shell('We\'ll be in touch to confirm your booking.', $body));
    }

    private static function otpBody(string $firstName, string $otpCode): string
    {
        $ink = self::BRAND_INK;
        $brass = self::BRAND_BRASS;
        $spaced = implode(' ', str_split($otpCode));
        return <<<HTML
        <p style="margin:0 0 16px; font-size:15px; color:{$ink}; line-height:1.6;">Hi {$firstName},</p>
        <p style="margin:0 0 24px; font-size:15px; color:{$ink}; line-height:1.6;">
          Use this code to reset your King&rsquo;s Cut Saloon password. It expires in 10 minutes.
        </p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center" style="padding:18px; background:#F7F1E6; border:1px dashed {$brass}; border-radius:8px;">
              <span style="font-family:'Courier New', monospace; font-size:32px; letter-spacing:8px; color:{$ink}; font-weight:bold;">{$spaced}</span>
            </td>
          </tr>
        </table>
        <p style="margin:24px 0 0; font-size:13px; color:#8A7A5C; line-height:1.6; font-family:Arial, sans-serif;">
          Didn&rsquo;t request this? You can safely ignore this email — your password won&rsquo;t change unless this code is used.
        </p>
        HTML;
    }

    private static function welcomeBody(string $firstName): string
    {
        $ink = self::BRAND_INK;
        return <<<HTML
        <p style="margin:0 0 16px; font-size:15px; color:{$ink}; line-height:1.6;">Hi {$firstName},</p>
        <p style="margin:0 0 16px; font-size:15px; color:{$ink}; line-height:1.6;">
          Welcome to King&rsquo;s Cut Saloon — your account is set up and ready to go.
        </p>
        <p style="margin:0 0 24px; font-size:15px; color:{$ink}; line-height:1.6;">
          Whenever you're ready, choose a membership plan to unlock your Membership ID, QR check-in, and wallet.
        </p>
        <p style="margin:0; font-size:13px; color:#8A7A5C; line-height:1.6; font-family:Arial, sans-serif;">
          The chair remembers your name. See you in the shop.
        </p>
        HTML;
    }

    private static function paymentBody(string $firstName, string $description, float $amount, string $method, string $dateLabel): string
    {
        $ink = self::BRAND_INK;
        $brass = self::BRAND_BRASS;
        $amountFormatted = money($amount);
        $methodLabel = match ($method) {
            'card' => 'Online — Card',
            'wallet' => 'Wallet',
            'manual_auth_code' => 'Manual (Auth Code)',
            default => ucfirst($method),
        };
        return <<<HTML
        <p style="margin:0 0 16px; font-size:15px; color:{$ink}; line-height:1.6;">Hi {$firstName},</p>
        <p style="margin:0 0 24px; font-size:15px; color:{$ink}; line-height:1.6;">
          This confirms a successful payment on your King&rsquo;s Cut Saloon account.
        </p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E3D9C4; border-radius:8px; overflow:hidden;">
          <tr>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">Description</td>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:14px; color:{$ink}; text-align:right;">{$description}</td>
          </tr>
          <tr>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">Amount</td>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:18px; color:{$brass}; text-align:right; font-weight:bold;">{$amountFormatted}</td>
          </tr>
          <tr>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">Method</td>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:14px; color:{$ink}; text-align:right;">{$methodLabel}</td>
          </tr>
          <tr>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">Date</td>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:14px; color:{$ink}; text-align:right;">{$dateLabel}</td>
          </tr>
        </table>
        <p style="margin:24px 0 0; font-size:13px; color:#8A7A5C; line-height:1.6; font-family:Arial, sans-serif;">
          You can view your full payment history any time from your dashboard.
        </p>
        HTML;
    }

    private static function reservationBody(string $firstName, string $sessionLabel, string $dateLabel, int $numberOfPeople, float $estimatedTotal): string
    {
        $ink = self::BRAND_INK;
        $brass = self::BRAND_BRASS;
        $amountFormatted = money($estimatedTotal);
        return <<<HTML
        <p style="margin:0 0 16px; font-size:15px; color:{$ink}; line-height:1.6;">Hi {$firstName},</p>
        <p style="margin:0 0 24px; font-size:15px; color:{$ink}; line-height:1.6;">
          Thanks for requesting a reservation at King&rsquo;s Cut Saloon. Our front desk will call or email you
          shortly to confirm the details below.
        </p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E3D9C4; border-radius:8px; overflow:hidden;">
          <tr>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">Session</td>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:14px; color:{$ink}; text-align:right;">{$sessionLabel}</td>
          </tr>
          <tr>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">Date</td>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:14px; color:{$ink}; text-align:right;">{$dateLabel}</td>
          </tr>
          <tr>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">People</td>
            <td style="padding:14px 18px; background:#F7F1E6; font-family:Arial, sans-serif; font-size:14px; color:{$ink}; text-align:right;">{$numberOfPeople}</td>
          </tr>
          <tr>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:12px; color:#8A7A5C; text-transform:uppercase; letter-spacing:0.5px;">Estimated Total</td>
            <td style="padding:14px 18px; font-family:Arial, sans-serif; font-size:18px; color:{$brass}; text-align:right; font-weight:bold;">{$amountFormatted}</td>
          </tr>
        </table>
        <p style="margin:24px 0 0; font-size:13px; color:#8A7A5C; line-height:1.6; font-family:Arial, sans-serif;">
          This is a request, not a confirmed booking — we'll reach out to lock in the exact time.
        </p>
        HTML;
    }
}
