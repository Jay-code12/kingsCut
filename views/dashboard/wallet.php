<?php
/**
 * @var array $wallet
 * @var array $transactions
 * @var array $subscriptions
 * @var int|null $selectedPlan
 */
?>
<div class="dash-head">
  <div><h3>Wallet</h3><p>Top up, track spend, and pay for services with your member discount.</p></div>
</div>

<div class="wallet-hero">
  <div><span class="lbl">Available balance</span><div class="amt"><?= money($wallet['balance']) ?></div>
    <?php if (count($subscriptions) > 1): ?><p class="progress-tiny" style="margin-top:4px;">Shared across all your plans</p><?php endif; ?>
  </div>
  <form method="post" action="<?= url('/dashboard/wallet/topup') ?>" style="display:flex; gap:10px;">
    <?php csrf_field(); ?>
    <input type="number" name="amount" min="100" step="100" placeholder="Amount ₦" required
           style="background:var(--panel); border:1px solid var(--line-strong); border-radius:8px; padding:12px 14px; color:var(--parchment); width:150px;">
    <button type="submit" class="btn btn-primary">Top Up</button>
  </form>
</div>
<p class="progress-tiny" style="margin:-10px 0 20px;">Demo top-up — no real payment gateway is wired up; the amount is credited immediately.</p>

<?php if (count($subscriptions) > 1): ?>
  <form method="get" action="<?= url('/dashboard/wallet') ?>" class="plan-filter-bar">
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
  <?php if ($selectedPlan !== null): ?>
    <p class="progress-tiny" style="margin-top:-10px; margin-bottom:16px;">Plain wallet top-ups aren't tied to a plan, so they're hidden while a specific plan is selected. Choose "All Plans" to see them.</p>
  <?php endif; ?>
<?php endif; ?>

<div class="panel-box">
  <h5>Recent transactions</h5>
  <?php if (empty($transactions)): ?>
    <p class="empty-note">No transactions <?= $selectedPlan !== null ? 'for this plan' : '' ?> yet.</p>
  <?php else: ?>
    <table class="data">
      <thead><tr><th>Date</th><th>Description</th><th>Type</th><?php if ($selectedPlan === null && count($subscriptions) > 1): ?><th>Plan</th><?php endif; ?><th style="text-align:right;">Amount</th></tr></thead>
      <tbody>
        <?php foreach ($transactions as $tx): ?>
          <tr>
            <td><?= e(date('M j, Y', strtotime($tx['created_at']))) ?></td>
            <td><?= e($tx['description']) ?></td>
            <td><?= e(ucwords(str_replace('_', ' ', $tx['reference_type']))) ?></td>
            <?php if ($selectedPlan === null && count($subscriptions) > 1): ?>
              <td><?= $tx['plan_name'] ? '<span class="plan-tag">' . e($tx['plan_name']) . '</span>' : '<span class="progress-tiny">—</span>' ?></td>
            <?php endif; ?>
            <td style="text-align:right; color:<?= $tx['type'] === 'credit' ? 'var(--sage)' : 'var(--burgundy-bright)' ?>;">
              <?= $tx['type'] === 'credit' ? '+' : '–' ?> <?= money($tx['amount']) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
