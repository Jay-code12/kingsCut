<?php

namespace App\Core;

use App\Models\Customer;

/**
 * Session-based auth helper for the Customer portal.
 */
class Auth
{
    public static function login(array $customer): void
    {
        session_regenerate_id(true);
        $_SESSION['customer_id'] = $customer['id'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['customer_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['customer_id'] ?? null;
    }

    /** Returns the logged-in customer row, or null. */
    public static function user(): ?array
    {
        static $cached = null;
        static $loaded = false;
        if (!self::check()) {
            return null;
        }
        if (!$loaded) {
            $cached = Customer::findById(self::id());
            $loaded = true;
        }
        return $cached;
    }

    /** Redirects to /login if not authenticated. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Please sign in to continue.');
            redirect('/login');
        }
    }

    /** Initials for the avatar badge, e.g. "Alex Morgan" -> "AM". */
    public static function initials(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName));
        $letters = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));
        return implode('', $letters) ?: '?';
    }
}
