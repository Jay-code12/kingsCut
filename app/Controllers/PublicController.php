<?php

namespace App\Controllers;

use App\Models\SecondaryId;
use App\Models\Subscription;

/**
 * Public, unauthenticated pages reachable via a shared QR/link.
 * No customer data beyond what's safe to show a guest is exposed here —
 * no wallet balance, no email, no phone number.
 */
class PublicController
{
    public function showId(string $token): void
    {
        $secondary = SecondaryId::findByQrToken($token);
        if ($secondary) {
            $title = 'Shared Membership ID';
            require __DIR__ . '/../../views/layout/auth_header.php';
            require __DIR__ . '/../../views/public_id.php';
            require __DIR__ . '/../../views/layout/auth_footer.php';
            return;
        }

        $subscription = Subscription::findByQrToken($token);
        if ($subscription) {
            $secondary = null;
            $title = 'Shared Membership Ticket';
            require __DIR__ . '/../../views/layout/auth_header.php';
            require __DIR__ . '/../../views/public_id.php';
            require __DIR__ . '/../../views/layout/auth_footer.php';
            return;
        }

        http_response_code(404);
        require __DIR__ . '/../../views/404.php';
    }
}
