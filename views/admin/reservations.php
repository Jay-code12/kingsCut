<?php
/**
 * @var array $reservations
 * @var string|null $statusFilter
 * @var int $pendingCount
 */
use App\Models\SessionPricing;
$statusOptions = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'];
?>
<div class="dash-head">
  <div><h3>Reservations</h3><p>Booking requests from the Reserve page — confirm or decline, and leave a note for the customer.</p></div>
  <?php if ($pendingCount > 0): ?><span class="status-chip status-temp"><?= $pendingCount ?> pending</span><?php endif; ?>
</div>

<div class="filter-bar">
  <a href="<?= url('/admin/reservations') ?>" style="text-decoration:none;"><button class="btn <?= $statusFilter === null ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</button></a>
  <?php foreach ($statusOptions as $key => $label): ?>
    <a href="<?= url('/admin/reservations?status=' . $key) ?>" style="text-decoration:none;"><button class="btn <?= $statusFilter === $key ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($label) ?></button></a>
  <?php endforeach; ?>
</div>

<?php if (empty($reservations)): ?>
  <p class="empty-note">No reservations <?= $statusFilter ? 'with that status' : '' ?> yet.</p>
<?php else: ?>
  <?php foreach ($reservations as $r): ?>
    <div class="reservation-row">
      <div class="reservation-row-head">
        <h5><?= e($r['full_name']) ?> <span class="progress-tiny">· <?= e(date('D, d M Y', strtotime($r['reservation_date']))) ?></span></h5>
        <span class="status-chip status-<?= $r['status'] === 'confirmed' ? 'active' : ($r['status'] === 'cancelled' ? 'expired' : 'temp') ?>"><?= e(ucfirst($r['status'])) ?></span>
      </div>

      <div class="reservation-meta-grid">
        <div><span>Session</span><?= e(SessionPricing::sessionLabel($r['session_type'])) ?> — <?= e(SessionPricing::locationLabel($r['location_type'])) ?></div>
        <div><span>People</span><?= (int) $r['number_of_people'] ?></div>
        <div><span>Estimated Total</span><?= money($r['estimated_total']) ?></div>
        <div><span>Requested</span><?= e(date('M j, Y g:ia', strtotime($r['created_at']))) ?></div>
        <div><span>Phone</span><?= e($r['phone']) ?></div>
        <div><span>Email</span><?= e($r['email']) ?></div>
        <?php if ($r['customer_id']): ?><div><span>Account</span>Logged-in member</div><?php endif; ?>
      </div>

      <?php if (!empty($r['services'])): ?>
        <div class="reservation-services-tags">
          <?php foreach ($r['services'] as $svc): ?>
            <span class="plan-tag"><?= e($svc['name']) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($r['notes'])): ?>
        <p class="progress-tiny" style="margin-bottom:12px;"><strong>Customer note:</strong> <?= e($r['notes']) ?></p>
      <?php endif; ?>
      <?php if (!empty($r['admin_note'])): ?>
        <p class="progress-tiny" style="margin-bottom:12px; color:var(--brass-bright);"><strong>Admin note:</strong> <?= e($r['admin_note']) ?></p>
      <?php endif; ?>

      <form method="post" action="<?= url('/admin/reservations/' . $r['id'] . '/status') ?>" class="reservation-status-form">
        <?php csrf_field(); ?>
        <select name="status">
          <?php foreach ($statusOptions as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $r['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="admin_note" placeholder="Note for the customer (optional)" value="<?= e($r['admin_note'] ?? '') ?>">
        <button type="submit" class="btn btn-outline btn-sm">Update</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
