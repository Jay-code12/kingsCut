<?php
/**
 * @var array $subscriptions
 * @var int|null $selectedPlan
 * @var array|null $subscription
 * @var array $history
 * @var array $daysVisited
 */
$today = new DateTimeImmutable('now');
$daysInMonth = (int) $today->format('t');
?>
<div class="dash-head">
  <div><h3>Attendance</h3><p>Every check-in, logged the moment your QR is scanned.</p></div>
</div>

<?php if (empty($subscriptions)): ?>
  <div class="empty-state">
    <h5>No membership yet</h5>
    <p>Attendance history appears here once you have an active membership.</p>
    <a href="<?= url('/membership') ?>" class="btn btn-primary">View Membership Plans</a>
  </div>
<?php else: ?>

  <?php if (count($subscriptions) > 1): ?>
    <form method="get" action="<?= url('/dashboard/attendance') ?>" class="plan-filter-bar">
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

  <div class="panel-box" style="margin-bottom:20px;">
    <h5><?= e($today->format('F Y')) ?></h5>
    <div class="attendance-strip">
      <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
        <div class="att-day <?= in_array($d, $daysVisited, true) ? 'visited' : '' ?>"><?= $d ?></div>
      <?php endfor; ?>
    </div>
  </div>

  <div class="panel-box">
    <h5>History</h5>
    <?php if (empty($history)): ?>
      <p class="empty-note">No visits logged yet.</p>
    <?php else: ?>
      <table class="data">
        <thead>
          <tr>
            <th>Date</th><th>Time</th>
            <?php if ($selectedPlan === null && count($subscriptions) > 1): ?><th>Plan</th><?php endif; ?>
            <th>ID Used</th><th>Verified By</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($history as $row): ?>
            <tr>
              <td><?= e(date('M j, Y', strtotime($row['checked_in_at']))) ?></td>
              <td><?= e(date('g:i A', strtotime($row['checked_in_at']))) ?></td>
              <?php if ($selectedPlan === null && count($subscriptions) > 1): ?>
                <td><span class="plan-tag"><?= e($row['plan_name']) ?></span></td>
              <?php endif; ?>
              <td><?= e($row['secondary_label'] ? $row['secondary_code'] . ' (' . $row['secondary_label'] . ')' : $row['membership_id'] . ' (Primary)') ?></td>
              <td>
                <?= match ($row['verified_by']) {
                    'qr_scan' => 'QR Scan',
                    'manual_entry' => 'Admin — Manual Entry',
                    'admin_override' => 'Admin Override',
                    default => e($row['verified_by']),
                } ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php endif; ?>
