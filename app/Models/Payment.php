<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Payment
{
    /**
     * Payments for a customer, optionally filtered to one or more subscriptions (plans).
     * Pass null for $subscriptionIds to see everything.
     */
    public static function forCustomer(int $customerId, int $limit = 50, ?array $subscriptionIds = null): array
    {
        $db = Database::getInstance();

        if ($subscriptionIds === null) {
            $stmt = $db->prepare(
                'SELECT pay.*, s.membership_id, p.name AS plan_name
                 FROM payments pay
                 LEFT JOIN subscriptions s ON s.id = pay.subscription_id
                 LEFT JOIN plans p ON p.id = s.plan_id
                 WHERE pay.customer_id = ?
                 ORDER BY pay.created_at DESC LIMIT ?'
            );
            $stmt->bindValue(1, $customerId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        if (empty($subscriptionIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $sql = "SELECT pay.*, s.membership_id, p.name AS plan_name
                FROM payments pay
                LEFT JOIN subscriptions s ON s.id = pay.subscription_id
                LEFT JOIN plans p ON p.id = s.plan_id
                WHERE pay.customer_id = ? AND pay.subscription_id IN ($placeholders)
                ORDER BY pay.created_at DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $customerId, PDO::PARAM_INT);
        $i = 2;
        foreach ($subscriptionIds as $id) {
            $stmt->bindValue($i++, $id, PDO::PARAM_INT);
        }
        $stmt->bindValue($i, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function record(
        int $customerId,
        ?int $subscriptionId,
        ?int $serviceId,
        string $description,
        float $amount,
        float $discountApplied,
        string $method
    ): int {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO payments (customer_id, subscription_id, service_id, description, amount, discount_applied, method, status)
             VALUES (?,?,?,?,?,?,?,"paid")'
        );
        $stmt->execute([$customerId, $subscriptionId, $serviceId, $description, $amount, $discountApplied, $method]);
        return (int) $db->lastInsertId();
    }
}
