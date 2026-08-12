<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Attendance
{
    /**
     * Check-in history across one or more subscriptions, joined with which
     * secondary ID (if any) was used and which plan it belongs to (useful
     * when several subscription IDs are passed at once, i.e. "All Plans").
     */
    public static function historyForSubscriptions(array $subscriptionIds, int $limit = 30): array
    {
        if (empty($subscriptionIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $sql = "SELECT a.*, si.label AS secondary_label, si.secondary_code,
                       s.membership_id, p.name AS plan_name
                FROM attendance a
                LEFT JOIN secondary_ids si ON si.id = a.secondary_id_id
                JOIN subscriptions s ON s.id = a.subscription_id
                JOIN plans p ON p.id = s.plan_id
                WHERE a.subscription_id IN ($placeholders)
                ORDER BY a.checked_in_at DESC
                LIMIT ?";
        $stmt = Database::getInstance()->prepare($sql);
        $i = 1;
        foreach ($subscriptionIds as $id) {
            $stmt->bindValue($i++, $id, PDO::PARAM_INT);
        }
        $stmt->bindValue($i, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count of visits within the current calendar month, across one or more subscriptions. */
    public static function countThisMonth(array $subscriptionIds): int
    {
        if (empty($subscriptionIds)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM attendance
             WHERE subscription_id IN ($placeholders)
               AND MONTH(checked_in_at) = MONTH(CURRENT_DATE())
               AND YEAR(checked_in_at) = YEAR(CURRENT_DATE())"
        );
        $stmt->execute($subscriptionIds);
        return (int) $stmt->fetchColumn();
    }

    /** Distinct calendar days visited in the current month, across one or more subscriptions. */
    public static function daysVisitedThisMonth(array $subscriptionIds): array
    {
        if (empty($subscriptionIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $stmt = Database::getInstance()->prepare(
            "SELECT DISTINCT DAY(checked_in_at) AS day FROM attendance
             WHERE subscription_id IN ($placeholders)
               AND MONTH(checked_in_at) = MONTH(CURRENT_DATE())
               AND YEAR(checked_in_at) = YEAR(CURRENT_DATE())"
        );
        $stmt->execute($subscriptionIds);
        return array_map('intval', array_column($stmt->fetchAll(), 'day'));
    }
}
