<?php

namespace App\Models;

use App\Core\Database;

class Coupon
{
    public static function all(): array
    {
        return Database::getInstance()->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM coupons WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM coupons WHERE code = ? LIMIT 1');
        $stmt->execute([strtoupper(trim($code))]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Validate a coupon for use right now. Returns the coupon row if valid,
     * or a ['error' => string] array explaining why not.
     */
    public static function validate(?string $code): array
    {
        if ($code === null || trim($code) === '') {
            return ['error' => null]; // no coupon attempted — not an error, just nothing to apply
        }

        $coupon = self::findByCode($code);
        if (!$coupon) {
            return ['error' => 'That coupon code was not found.'];
        }
        if (!$coupon['is_active']) {
            return ['error' => 'That coupon is no longer active.'];
        }
        if ($coupon['expires_at'] !== null && $coupon['expires_at'] < date('Y-m-d')) {
            return ['error' => 'That coupon has expired.'];
        }
        if ($coupon['max_uses'] !== null && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
            return ['error' => 'That coupon has reached its usage limit.'];
        }

        return $coupon;
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO coupons (code, discount_percent, max_uses, expires_at, is_active, created_by_admin_id)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            strtoupper(trim($data['code'])),
            (float) $data['discount_percent'],
            $data['max_uses'] !== '' && $data['max_uses'] !== null ? (int) $data['max_uses'] : null,
            $data['expires_at'] !== '' && $data['expires_at'] !== null ? $data['expires_at'] : null,
            !empty($data['is_active']) ? 1 : 0,
            $data['created_by_admin_id'] ?? null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function codeExists(string $code): bool
    {
        return self::findByCode($code) !== null;
    }

    public static function toggleActive(int $id, bool $active): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE coupons SET is_active = ? WHERE id = ?');
        $stmt->execute([$active ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM coupons WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Record a redemption: bumps used_count, and auto-deactivates the
     * coupon if that redemption reached its max_uses limit.
     */
    public static function redeem(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?');
        $stmt->execute([$id]);

        $stmt = $db->prepare('SELECT used_count, max_uses FROM coupons WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && $row['max_uses'] !== null && (int) $row['used_count'] >= (int) $row['max_uses']) {
            self::toggleActive($id, false);
        }
    }
}
