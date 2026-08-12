<?php
/** @var array $coupons */
?>
<div class="dash-head">
  <div><h3>Coupons</h3><p>Percentage-off codes for reservations only — never applied to membership plan purchases, since plan pricing is fixed when the plan is created.</p></div>
  <button class="btn btn-primary btn-sm" onclick="document.getElementById('addCouponForm').classList.toggle('show')">+ New Coupon</button>
</div>

<form id="addCouponForm" class="add-form" method="post" action="<?= url('/admin/coupons/create') ?>">
  <?php csrf_field(); ?>
  <div class="edit-row three">
    <div class="field"><label>Code</label><input type="text" name="code" placeholder="e.g. WELCOME10" required style="text-transform:uppercase;"></div>
    <div class="field"><label>Discount %</label><input type="number" name="discount_percent" min="1" max="100" step="1" placeholder="10" required></div>
    <div class="field"><label>Max uses (optional)</label><input type="number" name="max_uses" min="1" placeholder="Unlimited if blank"></div>
  </div>
  <div class="edit-row">
    <div class="field"><label>Expires on (optional)</label><input type="date" name="expires_at"></div>
  </div>
  <button type="submit" class="btn btn-primary">Create Coupon</button>
</form>

<?php if (empty($coupons)): ?>
  <p class="empty-note">No coupons yet.</p>
<?php else: ?>
  <div class="panel-box">
    <table class="data">
      <thead>
        <tr><th>Code</th><th>Discount</th><th>Usage</th><th>Expires</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($coupons as $c):
          $isExpired = $c['expires_at'] !== null && $c['expires_at'] < date('Y-m-d');
          $isMaxedOut = $c['max_uses'] !== null && (int) $c['used_count'] >= (int) $c['max_uses'];
        ?>
          <tr>
            <td class="mono"><?= e($c['code']) ?></td>
            <td><?= (float) $c['discount_percent'] ?>%</td>
            <td><?= (int) $c['used_count'] ?><?= $c['max_uses'] !== null ? ' / ' . (int) $c['max_uses'] : ' (unlimited)' ?></td>
            <td><?= $c['expires_at'] !== null ? e(date('M j, Y', strtotime($c['expires_at']))) : 'Never' ?></td>
            <td>
              <?php if (!$c['is_active']): ?>
                <span class="status-chip status-expired">Inactive</span>
              <?php elseif ($isExpired): ?>
                <span class="status-chip status-expired">Expired</span>
              <?php elseif ($isMaxedOut): ?>
                <span class="status-chip status-expired">Limit reached</span>
              <?php else: ?>
                <span class="status-chip status-active">Active</span>
              <?php endif; ?>
            </td>
            <td class="row-actions" style="display:flex; gap:8px;">
              <form method="post" action="<?= url('/admin/coupons/' . $c['id'] . '/toggle') ?>" class="inline">
                <?php csrf_field(); ?>
                <button type="submit" class="btn btn-outline btn-sm"><?= $c['is_active'] ? 'Deactivate' : 'Activate' ?></button>
              </form>
              <form method="post" action="<?= url('/admin/coupons/' . $c['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Delete this coupon?');">
                <?php csrf_field(); ?>
                <button type="submit" class="btn btn-outline btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
