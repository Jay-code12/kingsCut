<?php

namespace App\Models;

use App\Core\Database;

class Reservation
{
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO reservations
                (customer_id, full_name, email, phone, session_type, location_type, number_of_people,
                 reservation_date, notes, membership_id_input, membership_discount,
                 coupon_id, coupon_code, coupon_discount, estimated_total, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"pending")'
        );
        $stmt->execute([
            $data['customer_id'],
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['session_type'],
            $data['location_type'],
            $data['number_of_people'],
            $data['reservation_date'],
            $data['notes'],
            $data['membership_id_input'] ?? null,
            $data['membership_discount'] ?? 0,
            $data['coupon_id'] ?? null,
            $data['coupon_code'] ?? null,
            $data['coupon_discount'] ?? 0,
            $data['estimated_total'],
        ]);
        return (int) $db->lastInsertId();
    }

    public static function attachServices(int $reservationId, array $services): void
    {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO reservation_services (reservation_id, service_id, price_at_booking) VALUES (?,?,?)'
        );
        foreach ($services as $service) {
            $stmt->execute([$reservationId, $service['id'], $service['standard_price']]);
        }
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM reservations WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $reservation = $stmt->fetch();
        if (!$reservation) {
            return null;
        }
        $reservation['services'] = self::servicesFor($id);
        return $reservation;
    }

    public static function servicesFor(int $reservationId): array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT rs.price_at_booking, s.name
             FROM reservation_services rs
             JOIN services s ON s.id = rs.service_id
             WHERE rs.reservation_id = ?'
        );
        $stmt->execute([$reservationId]);
        return $stmt->fetchAll();
    }

    /**
     * All reservations for the admin list — newest submissions first.
     * Optionally filtered by status and/or a specific reservation date.
     */
    public static function allForAdmin(?string $status = null, ?string $date = null): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if ($status !== null) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($date !== null) {
            $where[] = 'reservation_date = ?';
            $params[] = $date;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $db->prepare("SELECT * FROM reservations $whereSql ORDER BY created_at DESC");
        $stmt->execute($params);

        $reservations = $stmt->fetchAll();
        foreach ($reservations as &$r) {
            $r['services'] = self::servicesFor((int) $r['id']);
        }
        return $reservations;
    }

    public static function countPending(): int
    {
        $stmt = Database::getInstance()->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'");
        return (int) $stmt->fetchColumn();
    }

    public static function updateStatus(int $id, string $status, ?string $adminNote): void
    {
        $stmt = Database::getInstance()->prepare(
            'UPDATE reservations SET status = ?, admin_note = ? WHERE id = ?'
        );
        $stmt->execute([$status, $adminNote, $id]);
    }
}
