<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Powers the Admin sales analytics view. Every method returns
 * ['labels' => [...], 'values' => [...], 'total' => float] with gaps
 * filled in as 0 so the chart always has a complete, evenly-spaced axis
 * regardless of which buckets actually had sales.
 */
class SalesReport
{
    /** Today, grouped by hour (0–23). */
    public static function byHour(): array
    {
        $stmt = Database::getInstance()->query(
            "SELECT HOUR(created_at) AS bucket, SUM(amount) AS total
             FROM payments
             WHERE status = 'paid' AND DATE(created_at) = CURDATE()
             GROUP BY HOUR(created_at)"
        );
        $rows = $stmt->fetchAll();
        $byBucket = array_column($rows, 'total', 'bucket');

        $labels = [];
        $values = [];
        foreach (range(0, 23) as $hour) {
            $labels[] = self::hourLabel($hour);
            $values[] = round((float) ($byBucket[$hour] ?? 0), 2);
        }
        return ['labels' => $labels, 'values' => $values, 'total' => array_sum($values)];
    }

    /** Last 30 days, grouped by calendar day. */
    public static function byDay(int $days = 30): array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT DATE(created_at) AS bucket, SUM(amount) AS total
             FROM payments
             WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)"
        );
        $stmt->execute([$days - 1]);
        $rows = $stmt->fetchAll();
        $byBucket = array_column($rows, 'total', 'bucket');

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
            $labels[] = (new \DateTimeImmutable($date))->format('M j');
            $values[] = round((float) ($byBucket[$date] ?? 0), 2);
        }
        return ['labels' => $labels, 'values' => $values, 'total' => array_sum($values)];
    }

    /** Last 12 weeks, grouped by ISO year-week. */
    public static function byWeek(int $weeks = 12): array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT YEARWEEK(created_at, 3) AS bucket, SUM(amount) AS total
             FROM payments
             WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
             GROUP BY YEARWEEK(created_at, 3)"
        );
        $stmt->execute([$weeks - 1]);
        $rows = $stmt->fetchAll();
        $byBucket = array_column($rows, 'total', 'bucket');

        $labels = [];
        $values = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $date = new \DateTimeImmutable("-{$i} weeks");
            $bucket = (int) $date->format('oW'); // ISO year + ISO week, matches YEARWEEK(...,3)
            $labels[] = 'Wk ' . $date->format('W');
            $values[] = round((float) ($byBucket[$bucket] ?? 0), 2);
        }
        return ['labels' => $labels, 'values' => $values, 'total' => array_sum($values)];
    }

    /** Last 12 months, grouped by calendar month. */
    public static function byMonth(int $months = 12): array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS bucket, SUM(amount) AS total
             FROM payments
             WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')"
        );
        $stmt->execute([$months - 1]);
        $rows = $stmt->fetchAll();
        $byBucket = array_column($rows, 'total', 'bucket');

        $labels = [];
        $values = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new \DateTimeImmutable("-{$i} months");
            $bucket = $date->format('Y-m');
            $labels[] = $date->format('M \'y');
            $values[] = round((float) ($byBucket[$bucket] ?? 0), 2);
        }
        return ['labels' => $labels, 'values' => $values, 'total' => array_sum($values)];
    }

    /** Last N years, grouped by calendar year. */
    public static function byYear(int $years = 5): array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT YEAR(created_at) AS bucket, SUM(amount) AS total
             FROM payments
             WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? YEAR)
             GROUP BY YEAR(created_at)"
        );
        $stmt->execute([$years - 1]);
        $rows = $stmt->fetchAll();
        $byBucket = array_column($rows, 'total', 'bucket');

        $labels = [];
        $values = [];
        $currentYear = (int) date('Y');
        for ($i = $years - 1; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $labels[] = (string) $year;
            $values[] = round((float) ($byBucket[$year] ?? 0), 2);
        }
        return ['labels' => $labels, 'values' => $values, 'total' => array_sum($values)];
    }

    public static function forRange(string $range): array
    {
        return match ($range) {
            'hour' => self::byHour(),
            'day' => self::byDay(),
            'week' => self::byWeek(),
            'year' => self::byYear(),
            default => self::byMonth(),
        };
    }

    /** Headline KPI numbers for the dashboard cards. */
    public static function summary(): array
    {
        $db = Database::getInstance();

        $today = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND DATE(created_at) = CURDATE()")->fetchColumn();
        $thisMonth = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
        $thisYear = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
        $allTime = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn();
        $txCount = $db->query("SELECT COUNT(*) FROM payments WHERE status='paid'")->fetchColumn();
        $activeMembers = $db->query("SELECT COUNT(DISTINCT customer_id) FROM subscriptions WHERE status='active'")->fetchColumn();
        $activePlans = $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();

        return [
            'today' => (float) $today,
            'this_month' => (float) $thisMonth,
            'this_year' => (float) $thisYear,
            'all_time' => (float) $allTime,
            'transaction_count' => (int) $txCount,
            'active_members' => (int) $activeMembers,
            'active_plans' => (int) $activePlans,
        ];
    }

    /** Revenue broken down by plan (for a quick mix view). */
    public static function revenueByPlan(): array
    {
        $stmt = Database::getInstance()->query(
            "SELECT p.name AS plan_name, SUM(pay.amount) AS total
             FROM payments pay
             JOIN subscriptions s ON s.id = pay.subscription_id
             JOIN plans p ON p.id = s.plan_id
             WHERE pay.status = 'paid'
             GROUP BY p.name
             ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }

    private static function hourLabel(int $hour): string
    {
        $period = $hour < 12 ? 'AM' : 'PM';
        $display = $hour % 12;
        if ($display === 0) {
            $display = 12;
        }
        return $display . $period;
    }
}
