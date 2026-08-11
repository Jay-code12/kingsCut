<?php

namespace App\Models;

use App\Core\Database;

class WorkItem
{
    public static function allPublic(int $limit = 100): array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM work_items ORDER BY sort_order ASC, created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function allForAdmin(): array
    {
        return Database::getInstance()->query('SELECT * FROM work_items ORDER BY sort_order ASC, created_at DESC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM work_items WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function createImage(string $title, string $imagePath, int $sortOrder = 0): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO work_items (type, title, image_path, sort_order) VALUES ("image", ?, ?, ?)'
        );
        $stmt->execute([$title ?: null, $imagePath, $sortOrder]);
        return (int) $db->lastInsertId();
    }

    public static function createVideo(string $title, string $youtubeUrl, string $youtubeVideoId, int $sortOrder = 0): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO work_items (type, title, youtube_url, youtube_video_id, sort_order) VALUES ("video", ?, ?, ?, ?)'
        );
        $stmt->execute([$title ?: null, $youtubeUrl, $youtubeVideoId, $sortOrder]);
        return (int) $db->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM work_items WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Extract the video ID from any common YouTube URL shape:
     * watch?v=ID, youtu.be/ID, embed/ID, shorts/ID — with optional query params.
     * Returns null if the URL doesn't look like YouTube.
     */
    public static function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '#youtu\.be/([A-Za-z0-9_-]{6,})#',
            '#youtube\.com/watch\?v=([A-Za-z0-9_-]{6,})#',
            '#youtube\.com/embed/([A-Za-z0-9_-]{6,})#',
            '#youtube\.com/shorts/([A-Za-z0-9_-]{6,})#',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
