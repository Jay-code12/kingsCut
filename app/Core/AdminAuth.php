<?php

namespace App\Core;

use App\Models\Admin;

/**
 * Session-based auth helper for the Admin console. Uses a separate session
 * key from the customer Auth class so an admin and a customer session can't
 * collide if somehow accessed from the same browser.
 */
class AdminAuth
{
    public static function login(array $admin): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_id']);
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    public static function user(): ?array
    {
        static $cached = null;
        static $loaded = false;
        if (!self::check()) {
            return null;
        }
        if (!$loaded) {
            $cached = Admin::findById(self::id());
            $loaded = true;
        }
        return $cached;
    }

    public static function isSuperAdmin(): bool
    {
        $admin = self::user();
        return $admin && $admin['role'] === 'super_admin';
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Please sign in to the admin console.');
            redirect('/admin/login');
        }
    }

    /** Redirects with an error if the current admin isn't a Super Admin. */
    public static function requireSuperAdmin(): void
    {
        self::requireLogin();
        if (!self::isSuperAdmin()) {
            flash('error', 'That section is limited to Super Admins.');
            redirect('/admin');
        }
    }

    public static function initials(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName));
        $letters = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));
        return implode('', $letters) ?: '?';
    }
}
