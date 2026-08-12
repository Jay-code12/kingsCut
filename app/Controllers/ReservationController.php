<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
<<<<<<< HEAD
use App\Models\Coupon;
use App\Models\Reservation;
use App\Models\ServiceCatalog;
use App\Models\SessionPricing;
use App\Models\Subscription;
=======
use App\Models\Reservation;
use App\Models\ServiceCatalog;
use App\Models\SessionPricing;
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1

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

<<<<<<< HEAD
    /** Live AJAX check as the customer types a Membership ID — never trusted again at submit time. */
    public function checkMembership(): void
    {
        header('Content-Type: application/json');

        $membershipId = trim((string) ($_GET['membership_id'] ?? ''));
        if ($membershipId === '') {
            echo json_encode(['valid' => false]);
            return;
        }

        $subscription = Subscription::findByMembershipId($membershipId);
        if (!$subscription || $subscription['status'] !== 'active') {
            echo json_encode(['valid' => false, 'message' => 'No active membership found with that ID.']);
            return;
        }

        echo json_encode([
            'valid' => true,
            'plan_name' => $subscription['plan_name'],
            'discount_percent' => (float) $subscription['discount_percent'],
        ]);
    }

    /** Live AJAX check as the customer types a coupon code — never trusted again at submit time. */
    public function checkCoupon(): void
    {
        header('Content-Type: application/json');

        $code = trim((string) ($_GET['code'] ?? ''));
        $result = Coupon::validate($code);

        if (isset($result['error'])) {
            if ($result['error'] === null) {
                echo json_encode(['valid' => false]);
            } else {
                echo json_encode(['valid' => false, 'message' => $result['error']]);
            }
            return;
        }

        echo json_encode([
            'valid' => true,
            'discount_percent' => (float) $result['discount_percent'],
        ]);
    }

=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
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
<<<<<<< HEAD
        $membershipIdInput = trim($_POST['membership_id'] ?? '');
        $couponCodeInput = trim($_POST['coupon_code'] ?? '');
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1

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

<<<<<<< HEAD
        // ---- Pricing, calculated entirely server-side ----
        $sessionTotal = SessionPricing::estimate($pricing, $numberOfPeople);
        $servicesTotal = array_sum(array_map(fn($s) => (float) $s['standard_price'], $selectedServices));

        // Optional membership discount — applies to the services portion only,
        // matching how the member rate works elsewhere in the app.
        $membershipDiscount = 0.0;
        $validMembershipId = null;
        if ($membershipIdInput !== '') {
            $subscription = Subscription::findByMembershipId($membershipIdInput);
            if ($subscription && $subscription['status'] === 'active') {
                $membershipDiscount = round($servicesTotal * ((float) $subscription['discount_percent'] / 100), 2);
                $validMembershipId = $subscription['membership_id'];
            }
        }

        $subtotalBeforeCoupon = $sessionTotal + ($servicesTotal - $membershipDiscount);

        // Optional coupon — applies to the whole reservation subtotal, on top of
        // any membership discount, but never touches membership plan purchases.
        $couponDiscount = 0.0;
        $coupon = null;
        if ($couponCodeInput !== '') {
            $couponResult = Coupon::validate($couponCodeInput);
            if (!isset($couponResult['error'])) {
                $coupon = $couponResult;
                $couponDiscount = round($subtotalBeforeCoupon * ((float) $coupon['discount_percent'] / 100), 2);
            }
        }

        $estimatedTotal = $subtotalBeforeCoupon - $couponDiscount;
=======
        $sessionTotal = SessionPricing::estimate($pricing, $numberOfPeople);
        $servicesTotal = array_sum(array_map(fn($s) => (float) $s['standard_price'], $selectedServices));
        $estimatedTotal = $sessionTotal + $servicesTotal;
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1

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
<<<<<<< HEAD
            'membership_id_input' => $validMembershipId,
            'membership_discount' => $membershipDiscount,
            'coupon_id' => $coupon['id'] ?? null,
            'coupon_code' => $coupon['code'] ?? null,
            'coupon_discount' => $couponDiscount,
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
            'estimated_total' => $estimatedTotal,
        ]);
        Reservation::attachServices($reservationId, $selectedServices);

<<<<<<< HEAD
        if ($coupon) {
            Coupon::redeem((int) $coupon['id']);
        }

=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
        flash('success', 'Thanks, ' . explode(' ', $fullName)[0] . '! Your request is in — we\'ll call or email you shortly to confirm.');
        redirect('/reserve');
    }
}
