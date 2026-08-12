<?php

namespace App\Models;

use App\Core\Database;

class Share
{
    private const CHANNELS = ['email', 'whatsapp', 'twitter', 'facebook', 'copy_link', 'native'];

    public static function isValidChannel(string $channel): bool
    {
        return in_array($channel, self::CHANNELS, true);
    }

    /** Log a share event. Exactly one of $subscriptionId / $secondaryIdId should be set. */
    public static function log(?int $subscriptionId, ?int $secondaryIdId, string $channel, ?string $recipient = null): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO id_shares (subscription_id, secondary_id_id, channel, recipient) VALUES (?,?,?,?)'
        );
        $stmt->execute([$subscriptionId, $secondaryIdId, $channel, $recipient]);
        return (int) $db->lastInsertId();
    }

    /**
     * Attempt to send the share link by email via PHP's mail().
     * Returns true if mail() accepted the message for delivery.
     * Note: XAMPP/local dev environments typically don't have an MTA
     * configured, so this will usually return false locally — see the
     * README for enabling outgoing mail (e.g. via Mercury Mail or an
     * SMTP relay plugin).
     */
    public static function sendEmail(string $toEmail, string $subject, string $shareUrl, string $label): bool
    {
        $body = "Hi,\n\n"
            . "You've been sent a King's Cut Saloon membership ID: {$label}.\n\n"
            . "View it and its check-in QR code here:\n{$shareUrl}\n\n"
            . "— King's Cut Saloon\n";

        $headers = "From: King's Cut Saloon <no-reply@kingscutsaloon.com>\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n";

        return @mail($toEmail, $subject, $body, $headers);
    }
}
