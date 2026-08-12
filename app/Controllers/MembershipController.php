<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\View;
use App\Models\Plan;
use App\Models\Subscription;

class MembershipController
{
    public function index(): void
    {
        $plans = Plan::allWithPrices();
        View::render('membership', [
            'title' => 'Membership & Plans',
            'plans' => $plans,
            'durations' => Plan::durations(),
        ]);
    }

    /**
     * Simulated online checkout — per the PRD, an online payment activates
     * the subscription automatically (no payment gateway is wired up here).
     */
    public function subscribe(): void
    {
        if (!Auth::check()) {
            flash('error', 'Create an account first, then choose your plan.');
            redirect('/register');
        }

        csrf_verify();

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $duration = $_POST['duration'] ?? 'monthly';

        $plan = Plan::find($planId);
        if (!$plan || !isset($plan['prices'][$duration])) {
            flash('error', 'That plan could not be found.');
            redirect('/membership');
        }

        if ($plan['is_custom_pricing']) {
            flash('error', 'Corporate plans are set up by our team — please use the Contact page.');
            redirect('/contact');
        }

        $price = (float) $plan['prices'][$duration]['price'];
        Subscription::create(Auth::id(), $plan, $duration, $price);

        $customer = Auth::user();
        $firstName = explode(' ', $customer['full_name'])[0];
        Mailer::sendPaymentReceipt(
            $customer['email'],
            $firstName,
            $plan['name'] . ' Plan — ' . ucfirst($duration) . ' subscription',
            $price,
            'card',
            date('M j, Y g:i A')
        );

        flash('success', 'Welcome aboard! Your ' . $plan['name'] . ' membership is now active.');
        redirect('/dashboard');
    }
}
