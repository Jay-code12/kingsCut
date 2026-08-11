<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\SessionPricing;

class AdminSessionController
{
    public function index(): void
    {
        AdminAuth::requireSuperAdmin();

        View::renderAdmin('sessions', [
            'title' => 'Booking Sessions',
            'admin' => AdminAuth::user(),
            'activeNav' => 'sessions',
            'pricing' => array_values(SessionPricing::allKeyed()),
        ]);
    }

    public function update(): void
    {
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
        redirect('/admin/sessions');
    }
}
