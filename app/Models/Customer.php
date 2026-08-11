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
}
