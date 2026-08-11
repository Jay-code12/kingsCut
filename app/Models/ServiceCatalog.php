<?php

namespace App\Models;

use App\Core\Database;

class ServiceCatalog
{
    /** Categories, each with a `services` array. */
    public static function allGrouped(): array
    {
        $db = Database::getInstance();
        $categories = $db->query('SELECT * FROM service_categories ORDER BY sort_order ASC')->fetchAll();
        $services = $db->query('SELECT * FROM services ORDER BY sort_order ASC')->fetchAll();

        $byCategory = [];
        foreach ($services as $service) {
            $byCategory[$service['category_id']][] = $service;
        }

        foreach ($categories as &$category) {
            $category['services'] = $byCategory[$category['id']] ?? [];
        }
        return $categories;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::getInstance()->query('SELECT * FROM services ORDER BY sort_order ASC')->fetchAll();
    }

    // ============================================================
    // Admin CRUD — Categories
    // ============================================================

    public static function allCategories(): array
    {
        return Database::getInstance()->query('SELECT * FROM service_categories ORDER BY sort_order ASC')->fetchAll();
    }

    public static function findCategory(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM service_categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function createCategory(string $name, int $sortOrder = 0): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO service_categories (name, sort_order) VALUES (?, ?)');
        $stmt->execute([$name, $sortOrder]);
        return (int) $db->lastInsertId();
    }

    public static function updateCategory(int $id, string $name, int $sortOrder): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE service_categories SET name = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$name, $sortOrder, $id]);
    }

    public static function deleteCategory(int $id): bool
    {
        // Refuse to delete a category that still has services — keeps the
        // customer-facing Services page from silently losing items.
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM services WHERE category_id = ?');
        $stmt->execute([$id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }
        $stmt = Database::getInstance()->prepare('DELETE FROM service_categories WHERE id = ?');
        $stmt->execute([$id]);
        return true;
    }

    // ============================================================
    // Admin CRUD — Services
    // ============================================================

    public static function createService(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO services (category_id, name, description, duration_minutes, standard_price, compare_at_price, sort_order)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            (int) $data['category_id'],
            $data['name'],
            $data['description'] ?: null,
            (int) $data['duration_minutes'],
            (float) $data['standard_price'],
            $data['compare_at_price'] !== '' && $data['compare_at_price'] !== null ? (float) $data['compare_at_price'] : null,
            (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) $db->lastInsertId();
    }

    public static function updateService(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare(
            'UPDATE services SET category_id = ?, name = ?, description = ?, duration_minutes = ?, standard_price = ?, compare_at_price = ?, sort_order = ?
             WHERE id = ?'
        );
        $stmt->execute([
            (int) $data['category_id'],
            $data['name'],
            $data['description'] ?: null,
            (int) $data['duration_minutes'],
            (float) $data['standard_price'],
            $data['compare_at_price'] !== '' && $data['compare_at_price'] !== null ? (float) $data['compare_at_price'] : null,
            (int) ($data['sort_order'] ?? 0),
            $id,
        ]);
    }

    public static function deleteService(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$id]);
    }
}
