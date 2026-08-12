<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Customer
{
    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $fullName, string $email, ?string $phone, string $passwordHash): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO customers (full_name, email, phone, password_hash) VALUES (?,?,?,?)'
        );
        $stmt->execute([$fullName, $email, $phone, $passwordHash]);
        return (int) $db->lastInsertId();
    }

    public static function updatePassword(int $customerId, string $newPasswordHash): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE customers SET password_hash = ? WHERE id = ?');
        $stmt->execute([$newPasswordHash, $customerId]);
    }
<<<<<<< HEAD

    public static function markEmailUnverified(int $customerId): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE customers SET email_verified_at = NULL WHERE id = ?');
        $stmt->execute([$customerId]);
    }

    /**
     * Customers for the Admin Console's Customers page, with a plan summary
     * joined in and optional search + category filtering.
     *
     * @param string|null $search    matches against name/email/phone
     * @param string      $category  'all' | 'active_plan' | 'no_plan' | 'verified' | 'unverified'
     */
    public static function forAdmin(?string $search, string $category = 'all'): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $where[] = '(c.full_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)';
            $like = '%' . trim($search) . '%';
            array_push($params, $like, $like, $like);
        }

        $having = '';
        if ($category === 'active_plan') {
            $having = 'HAVING active_plans > 0';
        } elseif ($category === 'no_plan') {
            $having = 'HAVING active_plans = 0';
        } elseif ($category === 'verified') {
            $where[] = 'c.email_verified_at IS NOT NULL';
        } elseif ($category === 'unverified') {
            $where[] = 'c.email_verified_at IS NULL';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT c.*,
                       COUNT(DISTINCT CASE WHEN s.status = 'active' THEN s.id END) AS active_plans,
                       COUNT(DISTINCT s.id) AS total_plans,
                       COALESCE(SUM(w.balance), 0) AS wallet_balance
                FROM customers c
                LEFT JOIN subscriptions s ON s.customer_id = c.id
                LEFT JOIN wallets w ON w.customer_id = c.id
                $whereSql
                GROUP BY c.id
                $having
                ORDER BY c.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Customers matching the given IDs — used to validate a bulk-email selection server-side. */
    public static function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare("SELECT * FROM customers WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
}
