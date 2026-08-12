<?php

namespace App\Models;

use App\Core\Database;

class EmailVerification
{
    private const OTP_TTL_MINUTES = 15;
    private const MAX_ATTEMPTS = 5;

    /** Generate a new 6-digit OTP for this customer, invalidating any earlier unused ones. Returns the plain code (never stored). */
    public static function generate(int $customerId): string
    {
        $db = Database::getInstance();

        $stmt = $db->prepare('UPDATE email_verifications SET used_at = NOW() WHERE customer_id = ? AND used_at IS NULL');
        $stmt->execute([$customerId]);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = hash('sha256', $otp);
        $expiresAt = (new \DateTimeImmutable('now'))->modify('+' . self::OTP_TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s');

        $stmt = $db->prepare(
            'INSERT INTO email_verifications (customer_id, otp_hash, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$customerId, $hash, $expiresAt]);

        return $otp;
    }

    /**
     * Verify a submitted OTP for this customer.
     * Returns 'ok', 'invalid', 'expired', or 'too_many_attempts'.
     */
    public static function verify(int $customerId, string $otpCode): string
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT * FROM email_verifications
             WHERE customer_id = ? AND used_at IS NULL
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$customerId]);
        $verification = $stmt->fetch();

        if (!$verification) {
            return 'invalid';
        }
        if ((int) $verification['attempts'] >= self::MAX_ATTEMPTS) {
            return 'too_many_attempts';
        }
        if (strtotime($verification['expires_at']) < time()) {
            return 'expired';
        }

        if (!hash_equals($verification['otp_hash'], hash('sha256', $otpCode))) {
            $stmt = $db->prepare('UPDATE email_verifications SET attempts = attempts + 1 WHERE id = ?');
            $stmt->execute([$verification['id']]);
            return 'invalid';
        }

        $stmt = $db->prepare('UPDATE email_verifications SET used_at = NOW() WHERE id = ?');
        $stmt->execute([$verification['id']]);

        $stmt = $db->prepare('UPDATE customers SET email_verified_at = NOW() WHERE id = ?');
        $stmt->execute([$customerId]);

        return 'ok';
    }
}
