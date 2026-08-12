<?php
/**
 * @var array $payments
 * @var array $subscriptions
 * @var int|null $selectedPlan
 */
?>
<div class="dash-head">
  <div><h3>Payments</h3><p>Subscription charges and service payments, in one ledger.</p></div>
</div>

<?php if (count($subscriptions) > 1): ?>
  <form method="get" action="<?= url('/dashboard/payments') ?>" class="plan-filter-bar">
    <label for="planFilter">Plan</label>
    <select name="plan" id="planFilter" onchange="this.form.submit()">
      <option value="all" <?= $selectedPlan === null ? 'selected' : '' ?>>All Plans</option>
      <?php foreach ($subscriptions as $s): ?>
        <option value="<?= (int) $s['id'] ?>" <?= $selectedPlan === (int) $s['id'] ? 'selected' : '' ?>>
          <?= e($s['plan_name']) ?> — <?= e($s['membership_id']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>
<?php endif; ?>

<div class="panel-box">
  <?php if (empty($payments)): ?>
    <p class="empty-note">No payments <?= $selectedPlan !== null ? 'for this plan' : '' ?> yet.</p>
  <?php else: ?>
    <table class="data">
      <thead>
        <tr>
          <th>Date</th><th>Description</th>
          <?php if ($selectedPlan === null && count($subscriptions) > 1): ?><th>Plan</th><?php endif; ?>
          <th>Method</th><th style="text-align:right;">Amount</th><th style="text-align:right;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= e(date('M j, Y', strtotime($p['created_at']))) ?></td>
            <td><?= e($p['description']) ?></td>
            <?php if ($selectedPlan === null && count($subscriptions) > 1): ?>
              <td><?= $p['plan_name'] ? '<span class="plan-tag">' . e($p['plan_name']) . '</span>' : '<span class="progress-tiny">—</span>' ?></td>
            <?php endif; ?>
            <td><?= e(match ($p['method']) { 'card' => 'Online — Card', 'wallet' => 'Wallet', 'manual_auth_code' => 'Manual (Auth Code)', default => $p['method'] }) ?></td>
            <td style="text-align:right;"><?= money($p['amount']) ?></td>
            <td style="text-align:right;">
              <span class="status-chip status-<?= $p['status'] === 'paid' ? 'active' : ($p['status'] === 'pending' ? 'temp' : 'expired') ?>">
                <?= e(ucfirst($p['status'])) ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
