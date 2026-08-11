<?php

namespace App\Models;

use App\Core\Database;

class SecondaryId
{
    public static function forSubscription(int $subscriptionId): array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM secondary_ids WHERE subscription_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$subscriptionId]);
        return $stmt->fetchAll();
    }

    /** Secondary IDs across several subscriptions at once, with plan info joined — for the "All Plans" filter. */
    public static function forSubscriptions(array $subscriptionIds): array
    {
        if (empty($subscriptionIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $stmt = Database::getInstance()->prepare(
            "SELECT si.*, s.membership_id, p.name AS plan_name
             FROM secondary_ids si
             JOIN subscriptions s ON s.id = si.subscription_id
             JOIN plans p ON p.id = s.plan_id
             WHERE si.subscription_id IN ($placeholders)
             ORDER BY si.created_at DESC"
        );
        $stmt->execute($subscriptionIds);
        return $stmt->fetchAll();
    }

    public static function countActive(int $subscriptionId): int
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM secondary_ids WHERE subscription_id = ? AND status = 'active'"
        );
        $stmt->execute([$subscriptionId]);
        return (int) $stmt->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM secondary_ids WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Look up a secondary ID by its public QR token (used by the public /id/{token} share page). */
    public static function findByQrToken(string $token): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT si.*, s.membership_id, p.name AS plan_name
             FROM secondary_ids si
             JOIN subscriptions s ON s.id = si.subscription_id
             JOIN plans p ON p.id = s.plan_id
             WHERE si.qr_token = ? LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $subscriptionId, string $membershipId, string $label, string $type, ?int $maxUses, ?string $expiresAt): int
    {
        $db = Database::getInstance();
        $code = self::generateCode($membershipId, $subscriptionId);
        $qrToken = bin2hex(random_bytes(16));

        $stmt = $db->prepare(
            'INSERT INTO secondary_ids (subscription_id, label, secondary_code, qr_token, type, max_uses, expires_at, status)
             VALUES (?,?,?,?,?,?,?,"active")'
        );
        $stmt->execute([$subscriptionId, $label, $code, $qrToken, $type, $maxUses, $expiresAt]);
        return (int) $db->lastInsertId();
    }

    /** Revoke a secondary ID — only if its subscription is one of the given (customer-owned) subscription IDs. */
    public static function revoke(int $id, array $ownedSubscriptionIds): bool
    {
        if (empty($ownedSubscriptionIds)) {
            return false;
        }
        $placeholders = implode(',', array_fill(0, count($ownedSubscriptionIds), '?'));
        $stmt = Database::getInstance()->prepare(
            "UPDATE secondary_ids SET status = 'revoked' WHERE id = ? AND subscription_id IN ($placeholders)"
        );
        $stmt->execute(array_merge([$id], $ownedSubscriptionIds));
        return $stmt->rowCount() > 0;
    }

    private static function generateCode(string $membershipId, int $subscriptionId): string
    {
        // e.g. KC-0417-SG -> KC-0417-G{n}
        $prefix = preg_replace('/-[A-Z]{2}$/', '', $membershipId);
        $count = self::totalEver($subscriptionId) + 1;
        return "{$prefix}-G{$count}";
    }

    private static function totalEver(int $subscriptionId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM secondary_ids WHERE subscription_id = ?');
        $stmt->execute([$subscriptionId]);
        return (int) $stmt->fetchColumn();
    }
}
