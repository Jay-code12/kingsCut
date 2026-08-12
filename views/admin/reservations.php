<?php
/**
 * @var array $reservations
 * @var string|null $statusFilter
<<<<<<< HEAD
 * @var string|null $dateFilter
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
 * @var int $pendingCount
 */
use App\Models\SessionPricing;
$statusOptions = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'];
<<<<<<< HEAD

function reservationUrl(?string $status, ?string $date): string
{
    $params = [];
    if ($status !== null) $params['status'] = $status;
    if ($date !== null) $params['date'] = $date;
    return url('/admin/reservations') . ($params ? '?' . http_build_query($params) : '');
}
?>
<div class="dash-head">
  <div><h3>Reservations</h3><p>Booking requests from the Reserve page, newest first — confirm or decline, and leave a note for the customer.</p></div>
=======
?>
<div class="dash-head">
  <div><h3>Reservations</h3><p>Booking requests from the Reserve page — confirm or decline, and leave a note for the customer.</p></div>
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
  <?php if ($pendingCount > 0): ?><span class="status-chip status-temp"><?= $pendingCount ?> pending</span><?php endif; ?>
</div>

<div class="filter-bar">
<<<<<<< HEAD
  <a href="<?= e(reservationUrl(null, $dateFilter)) ?>" style="text-decoration:none;"><button class="btn <?= $statusFilter === null ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</button></a>
  <?php foreach ($statusOptions as $key => $label): ?>
    <a href="<?= e(reservationUrl($key, $dateFilter)) ?>" style="text-decoration:none;"><button class="btn <?= $statusFilter === $key ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($label) ?></button></a>
  <?php endforeach; ?>
</div>

<form method="get" action="<?= url('/admin/reservations') ?>" class="plan-filter-bar">
  <?php if ($statusFilter !== null): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
  <label for="dateFilter">Session date</label>
  <input type="date" name="date" id="dateFilter" value="<?= e($dateFilter ?? '') ?>" onchange="this.form.submit()">
  <?php if ($dateFilter !== null): ?>
    <a href="<?= e(reservationUrl($statusFilter, null)) ?>" class="btn btn-outline btn-sm" style="text-decoration:none;">Clear date</a>
  <?php endif; ?>
</form>

<?php if (empty($reservations)): ?>
  <p class="empty-note">No reservations match <?= $statusFilter || $dateFilter ? 'that filter' : '' ?> yet.</p>
=======
  <a href="<?= url('/admin/reservations') ?>" style="text-decoration:none;"><button class="btn <?= $statusFilter === null ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</button></a>
  <?php foreach ($statusOptions as $key => $label): ?>
    <a href="<?= url('/admin/reservations?status=' . $key) ?>" style="text-decoration:none;"><button class="btn <?= $statusFilter === $key ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($label) ?></button></a>
  <?php endforeach; ?>
</div>

<?php if (empty($reservations)): ?>
  <p class="empty-note">No reservations <?= $statusFilter ? 'with that status' : '' ?> yet.</p>
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
<?php else: ?>
  <?php foreach ($reservations as $r): ?>
    <div class="reservation-row">
      <div class="reservation-row-head">
<<<<<<< HEAD
        <h5><?= e($r['full_name']) ?> <span class="progress-tiny">· session on <?= e(date('D, d M Y', strtotime($r['reservation_date']))) ?></span></h5>
=======
        <h5><?= e($r['full_name']) ?> <span class="progress-tiny">· <?= e(date('D, d M Y', strtotime($r['reservation_date']))) ?></span></h5>
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
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
<<<<<<< HEAD
        <?php if (!empty($r['membership_id_input'])): ?>
          <div><span>Membership Discount</span><?= e($r['membership_id_input']) ?> — <?= money($r['membership_discount']) ?> off</div>
        <?php endif; ?>
        <?php if (!empty($r['coupon_code'])): ?>
          <div><span>Coupon</span><?= e($r['coupon_code']) ?> — <?= money($r['coupon_discount']) ?> off</div>
        <?php endif; ?>
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
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
