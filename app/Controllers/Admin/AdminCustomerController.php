<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\View;
use App\Models\Customer;
use App\Models\Subscription;

class AdminCustomerController
{
    private const VALID_CATEGORIES = ['all', 'active_plan', 'no_plan', 'verified', 'unverified'];

    public function index(): void
    {
        AdminAuth::requireSuperAdmin();

        $search = trim((string) ($_GET['q'] ?? ''));
        $category = $_GET['category'] ?? 'all';
        if (!in_array($category, self::VALID_CATEGORIES, true)) {
            $category = 'all';
        }

        View::renderAdmin('customers', [
            'title' => 'Customers',
            'admin' => AdminAuth::user(),
            'activeNav' => 'customers',
            'customers' => Customer::forAdmin($search ?: null, $category),
            'search' => $search,
            'category' => $category,
        ]);
    }

    public function sendEmail(): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $customerIds = array_map('intval', $_POST['customer_ids'] ?? []);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($customerIds)) {
            flash('error', 'Select at least one customer.');
            redirect('/admin/customers');
        }
        if ($subject === '' || $message === '') {
            flash('error', 'Write a subject and a message before sending.');
            redirect('/admin/customers');
        }

        // Never trust the posted list blindly — re-fetch real customer rows for those IDs.
        $customers = Customer::findByIds($customerIds);
        if (empty($customers)) {
            flash('error', 'Those customers could not be found.');
            redirect('/admin/customers');
        }

        $sentCount = 0;
        foreach ($customers as $customer) {
            $firstName = explode(' ', $customer['full_name'])[0];
            if (Mailer::sendBroadcast($customer['email'], $firstName, $subject, $message)) {
                $sentCount++;
            }
        }

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO email_broadcasts (admin_id, subject, message, recipient_count) VALUES (?,?,?,?)'
        );
        $stmt->execute([AdminAuth::id(), $subject, $message, count($customers)]);

        if ($sentCount > 0) {
            flash('success', 'Sent to ' . $sentCount . ' of ' . count($customers) . ' selected customer(s).');
        } else {
            flash('error', 'Logged, but this server has no outgoing mail configured — nothing was actually delivered. See the README for enabling mail() locally.');
        }
        redirect('/admin/customers');
    }

    public function cards(string $customerId): void
    {
        AdminAuth::requireLogin();

        $customer = Customer::findById((int) $customerId);
        if (!$customer) {
            flash('error', 'Customer not found.');
            redirect('/admin/customers');
        }

        View::renderAdmin('customer-cards', [
            'title' => $customer['full_name'] . ' — Membership Cards',
            'admin' => AdminAuth::user(),
            'activeNav' => 'customers',
            'customer' => $customer,
            'subscriptions' => Subscription::allForCustomer((int) $customerId),
        ]);
    }

    /** Standalone, print-optimized membership card — not wrapped in the admin shell. */
    public function showCard(string $customerId, string $subscriptionId): void
    {
        AdminAuth::requireLogin();

        $customer = Customer::findById((int) $customerId);
        $subscription = Subscription::find((int) $subscriptionId);

        if (!$customer || !$subscription || (int) $subscription['customer_id'] !== (int) $customerId) {
            http_response_code(404);
            require __DIR__ . '/../../../views/404.php';
            return;
        }

        require __DIR__ . '/../../../views/admin/membership-card.php';
    }
}
