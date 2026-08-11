<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\SecondaryId;
use App\Models\Share;
use App\Models\Subscription;
use App\Models\Wallet;
use App\Core\View;

class DashboardController
{
    private function customer(): array
    {
        Auth::requireLogin();
        return Auth::user();
    }

    /**
     * Resolves which subscription(s) the current request is filtered to.
     * Returns [allSubscriptions, allSubscriptionIds, selectedId|null].
     * selectedId is null when the "All Plans" option is chosen (or there's
     * only one plan, or nothing was requested) — callers decide what "all"
     * means for their query (unfiltered vs. union across every owned id).
     */
    private function planFilterContext(int $customerId, ?string $requestedPlan): array
    {
        $subscriptions = Subscription::allForCustomer($customerId);
        $allIds = array_map(fn($s) => (int) $s['id'], $subscriptions);

        $selectedId = null;
        if ($requestedPlan !== null && $requestedPlan !== 'all' && in_array((int) $requestedPlan, $allIds, true)) {
            $selectedId = (int) $requestedPlan;
        }

        return [$subscriptions, $allIds, $selectedId];
    }

    /** Ownership check: is this secondary ID under any subscription this customer owns? */
    private function assertOwnsSecondaryId(int $secondaryId, array $ownedSubscriptionIds): ?array
    {
        $secondary = SecondaryId::find($secondaryId);
        if (!$secondary || !in_array((int) $secondary['subscription_id'], $ownedSubscriptionIds, true)) {
            return null;
        }
        return $secondary;
    }

    // ============================================================
    // OVERVIEW
    // ============================================================
    public function overview(): void
    {
        $customer = $this->customer();
        [$subscriptions, $allIds, $selectedId] = $this->planFilterContext($customer['id'], $_GET['plan'] ?? null);
        $wallet = Wallet::getOrCreateForCustomer($customer['id']);

        $subscription = null;
        if (!empty($subscriptions)) {
            $targetId = $selectedId ?? (int) $subscriptions[0]['id'];
            foreach ($subscriptions as $s) {
                if ((int) $s['id'] === $targetId) {
                    $subscription = $s;
                    break;
                }
            }
        }

        $secondaryIds = [];
        $visitsThisMonth = 0;
        $daysVisited = [];
        $activeSecondaryCount = 0;

        if ($subscription) {
            $secondaryIds = array_slice(SecondaryId::forSubscription($subscription['id']), 0, 3);
            $visitsThisMonth = Attendance::countThisMonth([$subscription['id']]);
            $daysVisited = Attendance::daysVisitedThisMonth([$subscription['id']]);
            $activeSecondaryCount = SecondaryId::countActive($subscription['id']);
        }

        View::renderDashboard('overview', [
            'title' => 'Overview',
            'customer' => $customer,
            'subscriptions' => $subscriptions,
            'subscription' => $subscription,
            'wallet' => $wallet,
            'secondaryIds' => $secondaryIds,
            'visitsThisMonth' => $visitsThisMonth,
            'daysVisited' => $daysVisited,
            'activeSecondaryCount' => $activeSecondaryCount,
            'activeNav' => 'overview',
        ]);
    }

    // ============================================================
    // WALLET
    // ============================================================
    public function wallet(): void
    {
        $customer = $this->customer();
        [$subscriptions, $allIds, $selectedId] = $this->planFilterContext($customer['id'], $_GET['plan'] ?? null);
        $wallet = Wallet::getOrCreateForCustomer($customer['id']);

        $filterIds = $selectedId !== null ? [$selectedId] : null; // null = unfiltered (incl. plain top-ups)
        $transactions = Wallet::transactions($wallet['id'], 20, $filterIds);

        View::renderDashboard('wallet', [
            'title' => 'Wallet',
            'customer' => $customer,
            'subscriptions' => $subscriptions,
            'selectedPlan' => $selectedId,
            'subscription' => $this->findSubscription($subscriptions, $selectedId),
            'wallet' => $wallet,
            'transactions' => $transactions,
            'activeNav' => 'wallet',
        ]);
    }

    public function topup(): void
    {
        $customer = $this->customer();
        csrf_verify();

        $amount = (float) ($_POST['amount'] ?? 0);
        if ($amount <= 0) {
            flash('error', 'Enter an amount greater than zero.');
            redirect('/dashboard/wallet');
        }

        $wallet = Wallet::getOrCreateForCustomer($customer['id']);
        Wallet::credit($wallet['id'], $amount, 'Wallet top-up via card (demo)', 'topup');

        flash('success', money($amount) . ' added to your wallet.');
        redirect('/dashboard/wallet');
    }

    // ============================================================
    // ATTENDANCE
    // ============================================================
    public function attendance(): void
    {
        $customer = $this->customer();
        [$subscriptions, $allIds, $selectedId] = $this->planFilterContext($customer['id'], $_GET['plan'] ?? null);

        $filterIds = $selectedId !== null ? [$selectedId] : $allIds;
        $history = Attendance::historyForSubscriptions($filterIds);
        $daysVisited = Attendance::daysVisitedThisMonth($filterIds);

        View::renderDashboard('attendance', [
            'title' => 'Attendance',
            'customer' => $customer,
            'subscriptions' => $subscriptions,
            'selectedPlan' => $selectedId,
            'subscription' => $this->findSubscription($subscriptions, $selectedId),
            'history' => $history,
            'daysVisited' => $daysVisited,
            'activeNav' => 'attendance',
        ]);
    }

    // ============================================================
    // FAMILY & GUEST IDs
    // ============================================================
    public function family(): void
    {
        $customer = $this->customer();
        [$subscriptions, $allIds, $selectedId] = $this->planFilterContext($customer['id'], $_GET['plan'] ?? null);

        $filterIds = $selectedId !== null ? [$selectedId] : $allIds;
        $secondaryIds = SecondaryId::forSubscriptions($filterIds);

        View::renderDashboard('family', [
            'title' => 'Family & Guest IDs',
            'customer' => $customer,
            'subscriptions' => $subscriptions,
            'selectedPlan' => $selectedId,
            'subscription' => $this->findSubscription($subscriptions, $selectedId),
            'secondaryIds' => $secondaryIds,
            'activeNav' => 'family',
        ]);
    }

    public function generateSecondaryId(): void
    {
        $customer = $this->customer();
        csrf_verify();

        $subscriptions = Subscription::allForCustomer($customer['id']);
        $subscriptionId = (int) ($_POST['subscription_id'] ?? 0);
        $subscription = $this->findSubscription($subscriptions, $subscriptionId);

        if (!$subscription) {
            flash('error', 'Choose which plan this ID belongs to.');
            redirect('/dashboard/family');
        }
        if ($subscription['status'] !== 'active') {
            flash('error', 'That plan is not active, so it can\'t issue new secondary IDs.');
            redirect('/dashboard/family?plan=' . $subscription['id']);
        }

        $activeCount = SecondaryId::countActive($subscription['id']);
        if ($activeCount >= (int) $subscription['max_secondary_ids']) {
            flash('error', 'Your ' . $subscription['plan_name'] . ' plan allows up to ' . $subscription['max_secondary_ids'] . ' secondary ID(s). Revoke one before adding another.');
            redirect('/dashboard/family?plan=' . $subscription['id']);
        }

        $label = trim($_POST['label'] ?? '');
        $type = ($_POST['type'] ?? 'permanent') === 'temporary' ? 'temporary' : 'permanent';
        $maxUses = null;
        $expiresAt = null;

        if ($type === 'temporary') {
            $maxUses = max(1, (int) ($_POST['max_uses'] ?? 3));
            $days = max(1, (int) ($_POST['expires_days'] ?? 30));
            $expiresAt = (new \DateTimeImmutable('now'))->modify("+{$days} days")->format('Y-m-d H:i:s');
        }

        if ($label === '') {
            flash('error', 'Give this ID a label (e.g. a name).');
            redirect('/dashboard/family?plan=' . $subscription['id']);
        }

        SecondaryId::create($subscription['id'], $subscription['membership_id'], $label, $type, $maxUses, $expiresAt);

        flash('success', 'New secondary ID generated for ' . $label . ' on your ' . $subscription['plan_name'] . ' plan.');
        redirect('/dashboard/family?plan=' . $subscription['id']);
    }

    public function revokeSecondaryId(string $id): void
    {
        $customer = $this->customer();
        csrf_verify();

        $subscriptions = Subscription::allForCustomer($customer['id']);
        $ownedIds = array_map(fn($s) => (int) $s['id'], $subscriptions);

        if (SecondaryId::revoke((int) $id, $ownedIds)) {
            flash('success', 'Secondary ID revoked.');
        } else {
            flash('error', 'Could not revoke that ID.');
        }
        redirect('/dashboard/family');
    }

    /**
     * Share a secondary ID's QR/link — logs the share, and sends an email
     * if channel=email. Called via fetch() from the dashboard, so it
     * responds with JSON rather than redirecting.
     */
    public function shareSecondaryId(string $id): void
    {
        $customer = $this->customer();
        header('Content-Type: application/json');

        $stored = $_SESSION['csrf_token'] ?? '';
        $submitted = $_POST['csrf_token'] ?? '';
        if ($stored === '' || $submitted === '' || !hash_equals($stored, $submitted)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Session expired — please reload the page.']);
            return;
        }

        $subscriptions = Subscription::allForCustomer($customer['id']);
        $ownedIds = array_map(fn($s) => (int) $s['id'], $subscriptions);
        $secondary = $this->assertOwnsSecondaryId((int) $id, $ownedIds);

        if (!$secondary) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'ID not found.']);
            return;
        }

        $channel = (string) ($_POST['channel'] ?? '');
        if (!Share::isValidChannel($channel)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Unknown share channel.']);
            return;
        }

        $shareUrl = url('/id/' . $secondary['qr_token']);
        $recipient = null;

        if ($channel === 'email') {
            $recipient = trim((string) ($_POST['recipient_email'] ?? ''));
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Enter a valid email address.']);
                return;
            }
            $sent = Share::sendEmail($recipient, "A King's Cut Saloon ID was shared with you", $shareUrl, $secondary['label']);
            Share::log(null, $secondary['id'], 'email', $recipient);

            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Emailed to ' . $recipient . '.']);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Logged, but this server has no outgoing mail configured — the link wasn\'t actually delivered. See the README for enabling mail() locally, or share the link directly for now.',
                    'shareUrl' => $shareUrl,
                ]);
            }
            return;
        }

        Share::log(null, $secondary['id'], $channel, null);
        echo json_encode(['success' => true, 'shareUrl' => $shareUrl]);
    }

    // ============================================================
    // PAYMENTS
    // ============================================================
    public function payments(): void
    {
        $customer = $this->customer();
        [$subscriptions, $allIds, $selectedId] = $this->planFilterContext($customer['id'], $_GET['plan'] ?? null);

        $filterIds = $selectedId !== null ? [$selectedId] : null;
        $payments = Payment::forCustomer($customer['id'], 50, $filterIds);

        View::renderDashboard('payments', [
            'title' => 'Payments',
            'customer' => $customer,
            'subscriptions' => $subscriptions,
            'selectedPlan' => $selectedId,
            'subscription' => $this->findSubscription($subscriptions, $selectedId),
            'payments' => $payments,
            'activeNav' => 'payments',
        ]);
    }

    /** Share a primary membership ticket's QR/link — same shape as shareSecondaryId(). */
    public function sharePrimary(string $subscriptionId): void
    {
        $customer = $this->customer();
        header('Content-Type: application/json');

        $stored = $_SESSION['csrf_token'] ?? '';
        $submitted = $_POST['csrf_token'] ?? '';
        if ($stored === '' || $submitted === '' || !hash_equals($stored, $submitted)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Session expired — please reload the page.']);
            return;
        }

        $subscriptions = Subscription::allForCustomer($customer['id']);
        $subscription = $this->findSubscription($subscriptions, (int) $subscriptionId);

        if (!$subscription) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Plan not found.']);
            return;
        }

        $channel = (string) ($_POST['channel'] ?? '');
        if (!Share::isValidChannel($channel)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Unknown share channel.']);
            return;
        }

        $shareUrl = url('/id/' . $subscription['qr_token']);

        if ($channel === 'email') {
            $recipient = trim((string) ($_POST['recipient_email'] ?? ''));
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Enter a valid email address.']);
                return;
            }
            $sent = Share::sendEmail($recipient, "A King's Cut Saloon membership ticket was shared with you", $shareUrl, $subscription['membership_id']);
            Share::log($subscription['id'], null, 'email', $recipient);

            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Emailed to ' . $recipient . '.']);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Logged, but this server has no outgoing mail configured — share the link directly for now.',
                    'shareUrl' => $shareUrl,
                ]);
            }
            return;
        }

        Share::log($subscription['id'], null, $channel, null);
        echo json_encode(['success' => true, 'shareUrl' => $shareUrl]);
    }

    private function findSubscription(array $subscriptions, ?int $id): ?array
    {
        if ($id === null) {
            return null;
        }
        foreach ($subscriptions as $s) {
            if ((int) $s['id'] === $id) {
                return $s;
            }
        }
        return null;
    }
}
