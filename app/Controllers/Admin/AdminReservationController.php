<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\Reservation;

class AdminReservationController
{
    private const VALID_STATUSES = ['pending', 'confirmed', 'cancelled'];

    public function index(): void
    {
        AdminAuth::requireLogin();

        $status = $_GET['status'] ?? null;
        if ($status !== null && !in_array($status, self::VALID_STATUSES, true)) {
            $status = null;
        }

        $date = $_GET['date'] ?? null;
        if ($date !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = null;
        }

        View::renderAdmin('reservations', [
            'title' => 'Reservations',
            'admin' => AdminAuth::user(),
            'activeNav' => 'reservations',
            'reservations' => Reservation::allForAdmin($status, $date),
            'statusFilter' => $status,
            'dateFilter' => $date,
            'pendingCount' => Reservation::countPending(),
        ]);
    }

    public function updateStatus(string $id): void
    {
        AdminAuth::requireLogin();
        csrf_verify();

        $status = $_POST['status'] ?? '';
        if (!in_array($status, self::VALID_STATUSES, true)) {
            flash('error', 'Unknown status.');
            redirect('/admin/reservations');
        }

        $note = trim($_POST['admin_note'] ?? '');
        Reservation::updateStatus((int) $id, $status, $note ?: null);

        flash('success', 'Reservation marked ' . $status . '.');
        redirect('/admin/reservations');
    }
}
