<?php

namespace App\Models;

use App\Core\Database;

class Subscription
{
    /** The customer's primary (most recent active) subscription, with plan info joined in. */
    public static function primaryForCustomer(int $customerId): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT s.*, p.name AS plan_name, p.code AS plan_code, p.discount_percent, p.max_secondary_ids
             FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.customer_id = ?
             ORDER BY (s.status = "active") DESC, s.created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$customerId]);
        return $stmt->fetch() ?: null;
    }

    public static function allForCustomer(int $customerId): array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT s.*, p.name AS plan_name, p.code AS plan_code, p.discount_percent, p.max_secondary_ids
             FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.customer_id = ?
             ORDER BY (s.status = "active") DESC, s.created_at DESC'
        );
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT s.*, p.name AS plan_name, p.discount_percent, p.max_secondary_ids
             FROM subscriptions s JOIN plans p ON p.id = s.plan_id
             WHERE s.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Look up a subscription by its public QR token (used by the public /id/{token} share page). */
    public static function findByQrToken(string $token): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT s.*, p.name AS plan_name
             FROM subscriptions s JOIN plans p ON p.id = s.plan_id
             WHERE s.qr_token = ? LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new subscription (simulated instant online payment activates it immediately,
     * matching the PRD: "Online payments activate subscriptions automatically").
     */
    public static function create(int $customerId, array $plan, string $duration, float $pricePaid): array
    {
        $db = Database::getInstance();

        $membershipId = self::generateMembershipId($plan['code']);
        $qrToken = bin2hex(random_bytes(16));
        $start = new \DateTimeImmutable('now');
        $end = $start->modify('+' . Plan::durationMonths($duration) . ' months');

        $stmt = $db->prepare(
            'INSERT INTO subscriptions
                (customer_id, plan_id, membership_id, qr_token, duration, price_paid, start_date, end_date, status)
             VALUES (?,?,?,?,?,?,?,?,"active")'
        );
        $stmt->execute([
            $customerId,
            $plan['id'],
            $membershipId,
            $qrToken,
            $duration,
            $pricePaid,
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
        ]);
        $subscriptionId = (int) $db->lastInsertId();

        Payment::record($customerId, $subscriptionId, null, $plan['name'] . ' Plan — ' . ucfirst($duration) . ' subscription', $pricePaid, 0, 'card');

        return self::find($subscriptionId);
    }

    private static function generateMembershipId(string $planCode): string
    {
        $suffix = match ($planCode) {
            'single'    => 'SG',
            'couple'    => 'CP',
            'family'    => 'FM',
            'corporate' => 'CO',
            default     => 'XX',
        };
        $number = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return "KC-{$number}-{$suffix}";
    }
}
