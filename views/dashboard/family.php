<?php
/**
 * @var array $subscriptions   all of the customer's plans (for the filter)
 * @var int|null $selectedPlan the currently filtered subscription id, or null = "All Plans"
 * @var array|null $subscription the resolved subscription for $selectedPlan
 * @var array $secondaryIds
 */
$hasAnyActive = false;
foreach ($subscriptions as $s) {
    if ($s['status'] === 'active') { $hasAnyActive = true; break; }
}
?>
<div class="dash-head">
  <div><h3>Family &amp; Guest IDs</h3><p>Generate a secondary Customer ID and QR for anyone who shares your membership.</p></div>
  <?php if ($hasAnyActive): ?>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('genForm').classList.toggle('show')">+ Generate New ID</button>
  <?php endif; ?>
</div>

<?php if (empty($subscriptions)): ?>
  <div class="empty-state">
    <h5>No membership yet</h5>
    <p>You'll be able to generate secondary IDs once you have an active membership.</p>
    <a href="<?= url('/membership') ?>" class="btn btn-primary">View Membership Plans</a>
  </div>
<?php else: ?>

  <?php if (count($subscriptions) > 1): ?>
    <form method="get" action="<?= url('/dashboard/family') ?>" class="plan-filter-bar">
      <label for="planFilter">Plan</label>
      <select name="plan" id="planFilter" onchange="this.form.submit()">
        <option value="all" <?= $selectedPlan === null ? 'selected' : '' ?>>All Plans</option>
        <?php foreach ($subscriptions as $s): ?>
          <option value="<?= (int) $s['id'] ?>" <?= $selectedPlan === (int) $s['id'] ? 'selected' : '' ?>>
            <?= e($s['plan_name']) ?> — <?= e($s['membership_id']) ?> (<?= e(ucfirst($s['status'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  <?php endif; ?>

  <?php if ($selectedPlan === null): ?>
    <p class="progress-tiny" style="margin-bottom:14px;">Showing secondary IDs across all your plans. Select one plan above to generate a new ID for it.</p>
  <?php elseif ($subscription):
    $activeInPlan = count(array_filter($secondaryIds, fn($s) => $s['status'] === 'active'));
  ?>
    <p class="progress-tiny" style="margin-bottom:14px;">
      <?= $activeInPlan ?> of <?= (int) $subscription['max_secondary_ids'] ?> secondary ID slot(s) in use on your <?= e($subscription['plan_name']) ?> plan.
    </p>
  <?php endif; ?>

  <?php if ($hasAnyActive): ?>
    <form class="gen-id-form" id="genForm" method="post" action="<?= url('/dashboard/family/generate') ?>">
      <?php csrf_field(); ?>
      <div class="field" style="grid-column: span 1;">
        <label>Plan</label>
        <select name="subscription_id" required>
          <?php foreach ($subscriptions as $s): if ($s['status'] !== 'active') continue; ?>
            <option value="<?= (int) $s['id'] ?>" <?= ($selectedPlan === (int) $s['id']) ? 'selected' : '' ?>>
              <?= e($s['plan_name']) ?> — <?= e($s['membership_id']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Label / name</label><input type="text" name="label" placeholder="e.g. Grace M., Guest Pass" required></div>
      <div class="field">
        <label>Type</label>
        <select name="type" id="typeSelect" onchange="document.getElementById('tempFields').style.display = this.value === 'temporary' ? 'flex' : 'none'">
          <option value="permanent">Permanent</option>
          <option value="temporary">Temporary</option>
        </select>
      </div>
      <div id="tempFields" style="display:none; gap:14px; grid-column: span 2;">
        <div class="field" style="flex:1;"><label>Max uses</label><input type="number" name="max_uses" min="1" value="3"></div>
        <div class="field" style="flex:1;"><label>Expires in (days)</label><input type="number" name="expires_days" min="1" value="30"></div>
      </div>
      <button type="submit" class="btn btn-primary">Generate</button>
    </form>
  <?php endif; ?>

  <div class="panel-box">
    <?php if (empty($secondaryIds)): ?>
      <p class="empty-note">No secondary IDs yet — generate one above.</p>
    <?php else: ?>
      <table class="data">
        <thead>
          <tr>
            <th>Label</th>
            <?php if ($selectedPlan === null): ?><th>Plan</th><?php endif; ?>
            <th>Type</th><th>Status</th><th>Last Used</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($secondaryIds as $sid): ?>
            <tr class="id-row <?= $sid['status'] !== 'active' ? 'revoked' : '' ?>">
              <td><?= e($sid['label']) ?> <span class="progress-tiny"><?= e($sid['secondary_code']) ?></span></td>
              <?php if ($selectedPlan === null): ?>
                <td><span class="plan-tag"><?= e($sid['plan_name'] ?? '') ?></span></td>
              <?php endif; ?>
              <td><?= e(ucfirst($sid['type'])) ?></td>
              <td>
                <?php if ($sid['status'] === 'active'):
                  $usesLeft = $sid['type'] === 'temporary' && $sid['max_uses'] ? max(0, $sid['max_uses'] - $sid['uses_count']) : null;
                ?>
                  <span class="status-chip status-active"><?= $usesLeft !== null ? $usesLeft . ' uses left' : 'Active' ?></span>
                <?php elseif ($sid['status'] === 'expired'): ?>
                  <span class="status-chip status-expired">Expired</span>
                <?php else: ?>
                  <span class="status-chip status-expired">Revoked</span>
                <?php endif; ?>
              </td>
              <td><?= $sid['last_used_at'] ? e(date('M j, Y', strtotime($sid['last_used_at']))) : '—' ?></td>
              <td class="row-actions" style="display:flex; gap:8px;">
                <button type="button" class="btn btn-outline btn-sm"
                  onclick='openShareModal({
                    label: <?= json_encode($sid['label']) ?>,
                    code: <?= json_encode($sid['secondary_code']) ?>,
                    planName: <?= json_encode($sid['plan_name'] ?? ($subscription['plan_name'] ?? "")) ?>,
                    status: <?= json_encode(ucfirst($sid['status'])) ?>,
                    qrToken: <?= json_encode($sid['qr_token']) ?>,
                    shareEndpoint: <?= json_encode(url('/dashboard/family/' . $sid['id'] . '/share')) ?>,
                    csrfToken: <?= json_encode(csrf_token()) ?>
                  })'>View / Share</button>
                <?php if ($sid['status'] === 'active'): ?>
                  <form class="inline" method="post" action="<?= url('/dashboard/family/revoke/' . $sid['id']) ?>" onsubmit="return confirm('Revoke this ID? It will stop working immediately.');">
                    <?php csrf_field(); ?>
                    <button type="submit" class="btn btn-outline btn-sm">Revoke</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php endif; ?>
