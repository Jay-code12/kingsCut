<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\Plan;

class AdminPlanController
{
    public function index(): void
    {
        AdminAuth::requireSuperAdmin();

        $plans = Plan::allWithPrices();

        View::renderAdmin('plans', [
            'title' => 'Membership Plans',
            'admin' => AdminAuth::user(),
            'activeNav' => 'plans',
            'plans' => $plans,
            'durations' => Plan::durations(),
        ]);
    }

    public function create(): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $name = trim($_POST['name'] ?? '');
        $code = trim(strtolower($_POST['code'] ?? ''));
        $code = preg_replace('/[^a-z0-9_]/', '', $code);

        if ($name === '' || $code === '') {
            flash('error', 'A plan needs both a name and a short code (letters/numbers only).');
            redirect('/admin/plans');
        }
        if (Plan::codeExists($code)) {
            flash('error', 'That plan code is already in use — choose a different one.');
            redirect('/admin/plans');
        }

        Plan::create([
            'code' => $code,
            'name' => $name,
            'tagline' => trim($_POST['tagline'] ?? ''),
            'max_secondary_ids' => $_POST['max_secondary_ids'] ?? 0,
            'discount_percent' => $_POST['discount_percent'] ?? 0,
            'is_featured' => !empty($_POST['is_featured']),
            'is_custom_pricing' => !empty($_POST['is_custom_pricing']),
            'sort_order' => $_POST['sort_order'] ?? 0,
        ]);

        flash('success', 'New plan "' . $name . '" created — set its pricing below.');
        redirect('/admin/plans');
    }

    public function update(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $planId = (int) $id;
        $plan = Plan::find($planId);
        if (!$plan) {
            flash('error', 'Plan not found.');
            redirect('/admin/plans');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', 'The plan name can\'t be empty.');
            redirect('/admin/plans');
        }

        Plan::update($planId, [
            'name' => $name,
            'tagline' => trim($_POST['tagline'] ?? ''),
            'max_secondary_ids' => $_POST['max_secondary_ids'] ?? 0,
            'discount_percent' => $_POST['discount_percent'] ?? 0,
            'is_featured' => !empty($_POST['is_featured']),
            'is_custom_pricing' => !empty($_POST['is_custom_pricing']),
            'sort_order' => $_POST['sort_order'] ?? 0,
        ]);

        foreach (Plan::durations() as $duration) {
            if (!isset($_POST['price'][$duration])) {
                continue;
            }
            $price = (float) $_POST['price'][$duration];
            $compareRaw = trim((string) ($_POST['compare_at_price'][$duration] ?? ''));
            $compareAt = $compareRaw === '' ? null : (float) $compareRaw;

            // A strike price only makes sense above the real price — ignore it otherwise
            // rather than silently displaying a confusing "discount" that isn't one.
            if ($compareAt !== null && $compareAt <= $price) {
                $compareAt = null;
            }

            Plan::upsertPrice($planId, $duration, $price, $compareAt);
        }

        flash('success', $name . ' plan updated — changes are live on the Membership page now.');
        redirect('/admin/plans');
    }
<<<<<<< HEAD

    public function delete(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $planId = (int) $id;
        $plan = Plan::find($planId);
        if (!$plan) {
            flash('error', 'Plan not found.');
            redirect('/admin/plans');
        }

        if (Plan::delete($planId)) {
            flash('success', $plan['name'] . ' plan deleted.');
        } else {
            flash('error', 'Can\'t delete ' . $plan['name'] . ' — it has customer subscriptions on record. Consider hiding it instead by moving it to the bottom or marking it custom-pricing.');
        }
        redirect('/admin/plans');
    }
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
}
