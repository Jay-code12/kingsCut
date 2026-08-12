<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\Coupon;

class AdminCouponController
{
    public function index(): void
    {
        AdminAuth::requireSuperAdmin();

        View::renderAdmin('coupons', [
            'title' => 'Coupons',
            'admin' => AdminAuth::user(),
            'activeNav' => 'coupons',
            'coupons' => Coupon::all(),
        ]);
    }

    public function create(): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $code = preg_replace('/[^A-Z0-9_-]/', '', $code);
        $discountPercent = (float) ($_POST['discount_percent'] ?? 0);

        if ($code === '') {
            flash('error', 'Give the coupon a code.');
            redirect('/admin/coupons');
        }
        if ($discountPercent <= 0 || $discountPercent > 100) {
            flash('error', 'Discount must be between 1 and 100%.');
            redirect('/admin/coupons');
        }
        if (Coupon::codeExists($code)) {
            flash('error', 'That code is already in use — choose a different one.');
            redirect('/admin/coupons');
        }

        Coupon::create([
            'code' => $code,
            'discount_percent' => $discountPercent,
            'max_uses' => $_POST['max_uses'] ?? '',
            'expires_at' => $_POST['expires_at'] ?? '',
            'is_active' => true,
            'created_by_admin_id' => AdminAuth::id(),
        ]);

        flash('success', 'Coupon ' . $code . ' created.');
        redirect('/admin/coupons');
    }

    public function toggle(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $coupon = Coupon::find((int) $id);
        if (!$coupon) {
            flash('error', 'Coupon not found.');
            redirect('/admin/coupons');
        }

        Coupon::toggleActive((int) $id, !$coupon['is_active']);
        flash('success', $coupon['code'] . ($coupon['is_active'] ? ' deactivated.' : ' activated.'));
        redirect('/admin/coupons');
    }

    public function delete(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        Coupon::delete((int) $id);
        flash('success', 'Coupon deleted.');
        redirect('/admin/coupons');
    }
}
