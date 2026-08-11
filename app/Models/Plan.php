<?php

namespace App\Models;

use App\Core\Database;

class Plan
{
    private const DURATIONS = ['monthly', '3m', '6m', 'yearly'];
    private const DURATION_LABELS = [
        'monthly' => '/ month',
        '3m'      => '/ 3 months',
        '6m'      => '/ 6 months',
        'yearly'  => '/ year',
    ];

    /** All plans, each with a `prices` array keyed by duration => ['price' => ..., 'compare_at_price' => ...]. */
    public static function allWithPrices(): array
    {
        $db = Database::getInstance();
        $plans = $db->query('SELECT * FROM plans ORDER BY sort_order ASC')->fetchAll();

        $priceRows = $db->query('SELECT * FROM plan_prices')->fetchAll();
        $pricesByPlan = [];
        foreach ($priceRows as $row) {
            $pricesByPlan[$row['plan_id']][$row['duration']] = [
                'price' => (float) $row['price'],
                'compare_at_price' => $row['compare_at_price'] !== null ? (float) $row['compare_at_price'] : null,
            ];
        }

        foreach ($plans as &$plan) {
            $plan['prices'] = $pricesByPlan[$plan['id']] ?? [];
        }
        return $plans;
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM plans WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $plan = $stmt->fetch();
        if (!$plan) {
            return null;
        }
        $stmt = $db->prepare('SELECT duration, price, compare_at_price FROM plan_prices WHERE plan_id = ?');
        $stmt->execute([$id]);
        $prices = [];
        foreach ($stmt->fetchAll() as $row) {
            $prices[$row['duration']] = [
                'price' => (float) $row['price'],
                'compare_at_price' => $row['compare_at_price'] !== null ? (float) $row['compare_at_price'] : null,
            ];
        }
        $plan['prices'] = $prices;
        return $plan;
    }

    public static function durations(): array
    {
        return self::DURATIONS;
    }

    public static function durationLabel(string $duration): string
    {
        return self::DURATION_LABELS[$duration] ?? '';
    }

    public static function durationMonths(string $duration): int
    {
        return match ($duration) {
            'monthly' => 1,
            '3m'      => 3,
            '6m'      => 6,
            'yearly'  => 12,
            default   => 1,
        };
    }

    // ============================================================
    // Admin CRUD
    // ============================================================

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO plans (code, name, tagline, max_secondary_ids, discount_percent, is_featured, is_custom_pricing, sort_order)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['code'],
            $data['name'],
            $data['tagline'] ?: null,
            (int) $data['max_secondary_ids'],
            (float) $data['discount_percent'],
            !empty($data['is_featured']) ? 1 : 0,
            !empty($data['is_custom_pricing']) ? 1 : 0,
            (int) ($data['sort_order'] ?? 0),
        ]);
        $planId = (int) $db->lastInsertId();

        foreach (self::DURATIONS as $duration) {
            $stmt = $db->prepare('INSERT INTO plan_prices (plan_id, duration, price, compare_at_price) VALUES (?,?,0,NULL)');
            $stmt->execute([$planId, $duration]);
        }

        return $planId;
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare(
            'UPDATE plans SET name = ?, tagline = ?, max_secondary_ids = ?, discount_percent = ?, is_featured = ?, is_custom_pricing = ?, sort_order = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['tagline'] ?: null,
            (int) $data['max_secondary_ids'],
            (float) $data['discount_percent'],
            !empty($data['is_featured']) ? 1 : 0,
            !empty($data['is_custom_pricing']) ? 1 : 0,
            (int) ($data['sort_order'] ?? 0),
            $id,
        ]);
    }

    /** Create-or-update the price row for one plan + duration. */
    public static function upsertPrice(int $planId, string $duration, float $price, ?float $compareAtPrice): void
    {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO plan_prices (plan_id, duration, price, compare_at_price) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE price = VALUES(price), compare_at_price = VALUES(compare_at_price)'
        );
        $stmt->execute([$planId, $duration, $price, $compareAtPrice]);
    }

    public static function codeExists(string $code): bool
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM plans WHERE code = ?');
        $stmt->execute([$code]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
