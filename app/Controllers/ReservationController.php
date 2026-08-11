<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Reservation;
use App\Models\ServiceCatalog;
use App\Models\SessionPricing;

class ReservationController
{
    public function showForm(): void
    {
        $user = Auth::user();

        View::render('reserve', [
            'title' => 'Book a Session',
            'categories' => ServiceCatalog::allGrouped(),
            'sessionOptions' => SessionPricing::activeOnly(),
            'user' => $user,
        ]);
    }

    public function submit(): void
    {
        csrf_verify();

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $sessionType = $_POST['session_type'] ?? '';
        $locationType = $_POST['location_type'] ?? '';
        $numberOfPeople = max(1, (int) ($_POST['number_of_people'] ?? 1));
        $reservationDate = $_POST['reservation_date'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $serviceIds = array_map('intval', $_POST['services'] ?? []);

        if ($fullName === '' || $email === '' || $phone === '') {
            flash('error', 'Please fill in your name, email, and phone number.');
            redirect('/reserve');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'That email address doesn\'t look valid.');
            redirect('/reserve');
        }
        if (!in_array($sessionType, SessionPricing::SESSION_TYPES, true)) {
            flash('error', 'Please choose a session time.');
            redirect('/reserve');
        }
        if (!in_array($locationType, SessionPricing::LOCATION_TYPES, true)) {
            flash('error', 'Please choose VIP Office or VIP Outside.');
            redirect('/reserve');
        }
        $today = date('Y-m-d');
        if ($reservationDate === '' || $reservationDate < $today) {
            flash('error', 'Please choose a valid, upcoming date.');
            redirect('/reserve');
        }
        if (empty($serviceIds)) {
            flash('error', 'Select at least one service you\'d like.');
            redirect('/reserve');
        }

        $pricing = SessionPricing::find($sessionType, $locationType);
        if (!$pricing || !$pricing['is_active']) {
            flash('error', 'That session option isn\'t currently available — please choose another.');
            redirect('/reserve');
        }

        // Only trust services that actually exist — never trust prices from the form.
        $allServices = ServiceCatalog::all();
        $selectedServices = array_values(array_filter(
            $allServices,
            fn($s) => in_array((int) $s['id'], $serviceIds, true)
        ));
        if (empty($selectedServices)) {
            flash('error', 'Select at least one valid service.');
            redirect('/reserve');
        }

        $sessionTotal = SessionPricing::estimate($pricing, $numberOfPeople);
        $servicesTotal = array_sum(array_map(fn($s) => (float) $s['standard_price'], $selectedServices));
        $estimatedTotal = $sessionTotal + $servicesTotal;

        $user = Auth::user();

        $reservationId = Reservation::create([
            'customer_id' => $user['id'] ?? null,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'session_type' => $sessionType,
            'location_type' => $locationType,
            'number_of_people' => $numberOfPeople,
            'reservation_date' => $reservationDate,
            'notes' => $notes ?: null,
            'estimated_total' => $estimatedTotal,
        ]);
        Reservation::attachServices($reservationId, $selectedServices);

        flash('success', 'Thanks, ' . explode(' ', $fullName)[0] . '! Your request is in — we\'ll call or email you shortly to confirm.');
        redirect('/reserve');
    }
}
