<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\SalesReport;

class AdminDashboardController
{
    private const VALID_RANGES = ['hour', 'day', 'week', 'month', 'year'];

    public function overview(): void
    {
        AdminAuth::requireLogin();

        $range = $_GET['range'] ?? 'month';
        if (!in_array($range, self::VALID_RANGES, true)) {
            $range = 'month';
        }

        $chart = SalesReport::forRange($range);
        $summary = SalesReport::summary();
        $revenueByPlan = SalesReport::revenueByPlan();

        View::renderAdmin('dashboard', [
            'title' => 'Overview',
            'admin' => AdminAuth::user(),
            'activeNav' => 'dashboard',
            'range' => $range,
            'chart' => $chart,
            'summary' => $summary,
            'revenueByPlan' => $revenueByPlan,
        ]);
    }
}
