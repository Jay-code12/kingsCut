<?php

namespace App\Models;

use App\Core\Database;

class SessionPricing
{
    public const SESSION_TYPES = ['morning', 'afternoon', 'evening', 'whole_day'];
    public const LOCATION_TYPES = ['vip_office', 'vip_outside'];

    private const SESSION_LABELS = [
        'morning'   => 'Morning',
        'afternoon' => 'Afternoon',
        'evening'   => 'Evening',
        'whole_day' => 'Whole Day',
    ];

    private const LOCATION_LABELS = [
        'vip_office'  => 'VIP Office',
        'vip_outside' => 'VIP Outside',
    ];

    public static function sessionLabel(string $type): string
    {
        return self::SESSION_LABELS[$type] ?? $type;
    }

    public static function locationLabel(string $type): string
    {
        return self::LOCATION_LABELS[$type] ?? $type;
    }

    /** All 8 combinations, keyed as "session_type:location_type" for easy lookup. */
    public static function allKeyed(): array
    {
        $rows = Database::getInstance()->query('SELECT * FROM session_pricing ORDER BY session_type, location_type')->fetchAll();
        $keyed = [];
        foreach ($rows as $row) {
            $keyed[$row['session_type'] . ':' . $row['location_type']] = $row;
        }
        return $keyed;
    }

    public static function find(string $sessionType, string $locationType): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM session_pricing WHERE session_type = ? AND location_type = ? LIMIT 1'
        );
        $stmt->execute([$sessionType, $locationType]);
        return $stmt->fetch() ?: null;
    }

    /** Only the combinations an admin has enabled — what the public reservation form offers. */
    public static function activeOnly(): array
    {
        $stmt = Database::getInstance()->query(
            'SELECT * FROM session_pricing WHERE is_active = 1 ORDER BY session_type, location_type'
        );
        return $stmt->fetchAll();
    }

    public static function update(string $sessionType, string $locationType, float $basePrice, float $pricePerPerson, bool $isActive): void
    {
        $stmt = Database::getInstance()->prepare(
            'UPDATE session_pricing SET base_price = ?, price_per_person = ?, is_active = ? WHERE session_type = ? AND location_type = ?'
        );
        $stmt->execute([$basePrice, $pricePerPerson, $isActive ? 1 : 0, $sessionType, $locationType]);
    }

    /** Availability-only toggle — never touches pricing. Safe for the plain Admin role to call. */
    public static function toggleActive(string $sessionType, string $locationType, bool $isActive): void
    {
        $stmt = Database::getInstance()->prepare(
            'UPDATE session_pricing SET is_active = ? WHERE session_type = ? AND location_type = ?'
        );
        $stmt->execute([$isActive ? 1 : 0, $sessionType, $locationType]);
    }

    /** Estimate a total for a booking: base fee + (people × per-person fee). */
    public static function estimate(array $pricing, int $numberOfPeople): float
    {
        return (float) $pricing['base_price'] + ((float) $pricing['price_per_person'] * max(1, $numberOfPeople));
    }
}
