<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\SessionPricing;

class AdminSessionController
{
    public function index(): void
    {
        // Both roles can view/manage availability; only Super Admin edits pricing.
        AdminAuth::requireLogin();

        View::renderAdmin('sessions', [
            'title' => 'Booking Sessions',
            'admin' => AdminAuth::user(),
            'activeNav' => 'sessions',
            'pricing' => array_values(SessionPricing::allKeyed()),
            'isSuper' => AdminAuth::isSuperAdmin(),
        ]);
    }

    public function update(): void
    {
        AdminAuth::requireLogin();
        csrf_verify();

        $isSuper = AdminAuth::isSuperAdmin();

        foreach (SessionPricing::SESSION_TYPES as $sessionType) {
            foreach (SessionPricing::LOCATION_TYPES as $locationType) {
                $key = $sessionType . '_' . $locationType;
                $isActive = !empty($_POST['active'][$key]);

                if ($isSuper) {
                    // Super Admin submits price fields too — only they're rendered in the form.
                    if (!isset($_POST['base'][$key])) {
                        continue;
                    }
                    $basePrice = (float) $_POST['base'][$key];
                    $perPerson = (float) ($_POST['per_person'][$key] ?? 0);
                    SessionPricing::update($sessionType, $locationType, $basePrice, $perPerson, $isActive);
                } else {
                    // Plain Admin: availability only — pricing is never touched here,
                    // even if a request were crafted to include price fields.
                    SessionPricing::toggleActive($sessionType, $locationType, $isActive);
                }
            }
        }

        flash('success', $isSuper
            ? 'Session pricing updated — changes are live on the Reserve page now.'
            : 'Session availability updated.');
        redirect('/admin/sessions');
    }
}
