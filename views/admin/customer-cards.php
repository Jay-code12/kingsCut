<?php
/**
 * @var array $customer
 * @var array $subscriptions
 */
?>
<div class="dash-head">
  <div><h3><?= e($customer['full_name']) ?> — Membership Cards</h3><p><?= e($customer['email']) ?> · <?= e($customer['phone'] ?: 'No phone on file') ?></p></div>
  <a href="<?= url('/admin/customers') ?>" class="btn btn-outline btn-sm" style="text-decoration:none;">&larr; Back to Customers</a>
</div>

<?php if (empty($subscriptions)): ?>
  <p class="empty-note">This customer has no membership plans yet.</p>
<?php else: ?>
  <div class="edit-grid">
    <?php foreach ($subscriptions as $s): ?>
      <div class="edit-card <?= $s['status'] === 'active' ? 'featured' : '' ?>">
        <div class="edit-card-head">
          <h4><?= e($s['plan_name']) ?></h4>
          <span class="code-badge"><?= e($s['membership_id']) ?></span>
        </div>
        <p class="progress-tiny" style="margin-bottom:14px;">
          <?= e(ucfirst($s['duration'])) ?> · <?= e(ucfirst($s['status'])) ?> · expires <?= e(date('M j, Y', strtotime($s['end_date']))) ?>
        </p>
        <a href="<?= url('/admin/customers/' . $customer['id'] . '/card/' . $s['id']) ?>" target="_blank" class="btn btn-primary btn-block" style="text-decoration:none;">
          View / Print Card
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
