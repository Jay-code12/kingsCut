<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\SessionPricing;

class AdminSessionController
{
    public function index(): void
    {
<<<<<<< HEAD
        // Both roles can view/manage availability; only Super Admin edits pricing.
        AdminAuth::requireLogin();
=======
        AdminAuth::requireSuperAdmin();
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1

        View::renderAdmin('sessions', [
            'title' => 'Booking Sessions',
            'admin' => AdminAuth::user(),
            'activeNav' => 'sessions',
            'pricing' => array_values(SessionPricing::allKeyed()),
<<<<<<< HEAD
            'isSuper' => AdminAuth::isSuperAdmin(),
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
        ]);
    }

    public function update(): void
    {
<<<<<<< HEAD
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
=======
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        foreach (SessionPricing::SESSION_TYPES as $sessionType) {
            foreach (SessionPricing::LOCATION_TYPES as $locationType) {
                $key = $sessionType . '_' . $locationType;
                if (!isset($_POST['base'][$key])) {
                    continue;
                }
                $basePrice = (float) $_POST['base'][$key];
                $perPerson = (float) ($_POST['per_person'][$key] ?? 0);
                $isActive = !empty($_POST['active'][$key]);

                SessionPricing::update($sessionType, $locationType, $basePrice, $perPerson, $isActive);
            }
        }

        flash('success', 'Session pricing updated — changes are live on the Reserve page now.');
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
        redirect('/admin/sessions');
    }
}
