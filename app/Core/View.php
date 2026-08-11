<?php

namespace App\Core;

/**
 * Renders a view file inside the shared header/footer layout.
 * Dashboard views use a second layout (with sidebar + tab bar).
 */
class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../views/layout/header.php';
        require __DIR__ . '/../../views/' . $view . '.php';
        require __DIR__ . '/../../views/layout/footer.php';
    }

    public static function renderDashboard(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../views/layout/dashboard_header.php';
        require __DIR__ . '/../../views/dashboard/' . $view . '.php';
        require __DIR__ . '/../../views/layout/dashboard_footer.php';
    }

    public static function renderAuth(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../views/layout/auth_header.php';
        require __DIR__ . '/../../views/auth/' . $view . '.php';
        require __DIR__ . '/../../views/layout/auth_footer.php';
    }

    public static function renderAdmin(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../views/layout/admin_header.php';
        require __DIR__ . '/../../views/admin/' . $view . '.php';
        require __DIR__ . '/../../views/layout/admin_footer.php';
    }

    /** Admin login uses the same plain centered-card shell as the customer auth pages. */
    public static function renderAdminAuth(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../views/layout/auth_header.php';
        require __DIR__ . '/../../views/admin/' . $view . '.php';
        require __DIR__ . '/../../views/layout/auth_footer.php';
    }
}
