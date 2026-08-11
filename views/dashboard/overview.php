<?php
/**
 * @var array $subscriptions
 * @var array|null $subscription
 * @var array $wallet
 * @var array $secondaryIds
 * @var int $visitsThisMonth
 * @var array $daysVisited
 * @var int $activeSecondaryCount
 */
$today = new DateTimeImmutable('now');
$weekDayLetters = ['M','T','W','T','F','S','S'];
$todayDayOfMonth = (int) $today->format('j');
$mondayOffset = ((int) $today->format('N')) - 1; // 0 = Monday
?>
<div class="dash-head">
  <h3>Overview</h3>
  <p>Your membership, wallet, and check-in — all in one glance.</p>
  <?php if ($subscription): ?>
    <span class="status-chip status-<?= $subscription['status'] === 'active' ? 'active' : ($subscription['status'] === 'expired' ? 'expired' : 'temp') ?>">
      Membership <?= e(ucfirst($subscription['status'])) ?>
    </span>
  <?php endif; ?>
</div>

<?php if (!$subscription): ?>
  <div class="empty-state">
    <h5>No membership yet</h5>
    <p>Choose a plan to get your Membership ID, QR code, and wallet unlocked.</p>
    <a href="<?= url('/membership') ?>" class="btn btn-primary">View Membership Plans</a>
  </div>
<?php else: ?>

  <?php if (count($subscriptions) > 1): ?>
    <form method="get" action="<?= url('/dashboard') ?>" class="plan-filter-bar">
      <label for="planFilter">Viewing plan</label>
      <select name="plan" id="planFilter" onchange="this.form.submit()">
        <?php foreach ($subscriptions as $s): ?>
          <option value="<?= (int) $s['id'] ?>" <?= (int) $subscription['id'] === (int) $s['id'] ? 'selected' : '' ?>>
            <?= e($s['plan_name']) ?> — <?= e($s['membership_id']) ?> (<?= e(ucfirst($s['status'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <span class="progress-tiny">You have <?= count($subscriptions) ?> active plans — wallet balance is shared across all of them.</span>
    </form>
  <?php endif; ?>

  <div class="card-grid">
    <div class="dcard">
      <span class="lbl">Current Plan</span>
      <div class="big"><?= e($subscription['plan_name']) ?></div>
      <div class="sub"><?= e(ucfirst($subscription['duration'])) ?> · expires <?= e(date('d M Y', strtotime($subscription['end_date']))) ?></div>
    </div>
    <div class="dcard">
      <span class="lbl">Wallet Balance</span>
      <div class="big"><?= money($wallet['balance']) ?></div>
      <div class="sub">Top up any time from the Wallet tab</div>
    </div>
    <div class="dcard">
      <span class="lbl">Visits This Month</span>
      <div class="big"><?= (int) $visitsThisMonth ?></div>
      <div class="sub"><?= e($today->format('M Y')) ?> · this plan</div>
    </div>
    <div class="dcard">
      <span class="lbl">Active Secondary IDs</span>
      <div class="big"><?= (int) $activeSecondaryCount ?> / <?= (int) $subscription['max_secondary_ids'] ?></div>
      <div class="sub">Manage from Family &amp; Guest IDs</div>
    </div>
  </div>

  <div class="two-col">
    <div class="panel-box">
      <h5>Membership ticket</h5>
      <div class="ticket" style="box-shadow:none;">
        <div class="ticket-main">
          <div class="ticket-top-row">
            <div class="ticket-brand"><span class="crown-title mono">KC</span><span class="eyebrow" style="margin:0;">Primary</span></div>
            <span class="status-chip status-<?= $subscription['status'] === 'active' ? 'active' : 'expired' ?>"><?= e(ucfirst($subscription['status'])) ?></span>
          </div>
          <div class="ticket-name"><?= e($customer['full_name']) ?></div>
          <div class="ticket-id"><?= e($subscription['membership_id']) ?></div>
          <div class="ticket-meta">
            <div><span>Plan</span><b><?= e($subscription['plan_name']) ?> — <?= e(ucfirst($subscription['duration'])) ?></b></div>
            <div><span>Started</span><b><?= e(date('d M Y', strtotime($subscription['start_date']))) ?></b></div>
            <div><span>Expires</span><b><?= e(date('d M Y', strtotime($subscription['end_date']))) ?></b></div>
          </div>
        </div>
        <div class="ticket-stub">
          <span class="stub-label">Scan to<br>check in</span>
          <div class="qr" aria-hidden="true">
            <?php for ($i = 0; $i < 36; $i++): ?><i></i><?php endfor; ?>
          </div>
          <span class="stub-label">No. <?= e(str_pad((string) $subscription['id'], 6, '0', STR_PAD_LEFT)) ?></span>
        </div>
      </div>
      <div style="display:flex; gap:12px; margin-top:16px;">
        <button type="button" class="btn btn-outline btn-sm"
          onclick='openShareModal({
            label: <?= json_encode($customer["full_name"]) ?>,
            code: <?= json_encode($subscription["membership_id"]) ?>,
            planName: <?= json_encode($subscription["plan_name"] . " — " . ucfirst($subscription["duration"])) ?>,
            status: <?= json_encode(ucfirst($subscription["status"])) ?>,
            qrToken: <?= json_encode($subscription["qr_token"]) ?>,
            shareEndpoint: <?= json_encode(url("/dashboard/share/primary/" . $subscription["id"])) ?>,
            csrfToken: <?= json_encode(csrf_token()) ?>
          })'>Share QR</button>
        <a href="<?= url('/dashboard/wallet') ?>" class="btn btn-outline btn-sm" style="text-decoration:none;">Top Up Wallet</a>
      </div>
    </div>

    <div class="panel-box">
      <h5>This week's attendance</h5>
      <p style="font-size:13px; color:var(--parchment-dim); margin:0 0 6px;"><?= e($today->modify('monday this week')->format('M j')) ?> – <?= e($today->modify('sunday this week')->format('M j')) ?></p>
      <div class="attendance-strip">
        <?php for ($i = 0; $i < 7; $i++):
          $dayNum = $todayDayOfMonth - $mondayOffset + $i;
          $visited = in_array($dayNum, $daysVisited, true);
        ?>
          <div class="att-day <?= $visited ? 'visited' : '' ?>"><?= e($weekDayLetters[$i]) ?></div>
        <?php endfor; ?>
      </div>
      <hr class="rule" style="margin:20px 0;">
      <h5 style="margin-bottom:10px;">Recent secondary IDs <span class="progress-tiny">(this plan)</span></h5>
      <?php if (empty($secondaryIds)): ?>
        <p class="progress-tiny">No secondary IDs generated yet.</p>
      <?php else: ?>
        <table class="data">
          <?php foreach ($secondaryIds as $sid): ?>
            <tr>
              <td><?= e($sid['label']) ?> — <?= e(ucfirst($sid['type'])) ?></td>
              <td style="text-align:right;">
                <?php if ($sid['status'] === 'active'): ?>
                  <span class="status-chip status-active">Active</span>
                <?php elseif ($sid['status'] === 'expired'): ?>
                  <span class="status-chip status-expired">Expired</span>
                <?php else: ?>
                  <span class="status-chip status-expired">Revoked</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
      <a href="<?= url('/dashboard/family') ?>" class="btn btn-outline btn-sm btn-block" style="margin-top:14px; text-decoration:none;">Manage all IDs</a>
    </div>
  </div>

<?php endif; ?>
