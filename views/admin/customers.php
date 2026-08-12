<?php
/**
 * @var array $customers
 * @var string $search
 * @var string $category
 */
$categoryLabels = [
    'all' => 'All Customers',
    'active_plan' => 'Has Active Plan',
    'no_plan' => 'No Plan Yet',
    'verified' => 'Email Verified',
    'unverified' => 'Email Unverified',
];
?>
<div class="dash-head">
  <div><h3>Customers</h3><p>Search, filter, and email your customer base.</p></div>
</div>

<form method="get" action="<?= url('/admin/customers') ?>" class="filter-bar" style="align-items:center;">
  <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name, email, or phone…" style="background:var(--panel); border:1px solid var(--line-strong); border-radius:8px; padding:9px 14px; color:var(--parchment); font-size:13.5px; min-width:220px;">
  <select name="category" onchange="this.form.submit()" style="background:var(--panel); border:1px solid var(--line-strong); border-radius:8px; padding:9px 14px; color:var(--parchment); font-size:13.5px;">
    <?php foreach ($categoryLabels as $key => $label): ?>
      <option value="<?= e($key) ?>" <?= $category === $key ? 'selected' : '' ?>><?= e($label) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-outline btn-sm">Search</button>
  <?php if ($search !== '' || $category !== 'all'): ?>
    <a href="<?= url('/admin/customers') ?>" class="btn btn-outline btn-sm" style="text-decoration:none;">Clear</a>
  <?php endif; ?>
</form>

<?php if (empty($customers)): ?>
  <p class="empty-note">No customers match that search.</p>
<?php else: ?>
  <form method="post" action="<?= url('/admin/customers/send-email') ?>" id="bulkEmailForm">
    <?php csrf_field(); ?>

    <div class="panel-box" style="margin-bottom:20px;">
      <table class="data">
        <thead>
          <tr>
            <th style="width:32px;"><input type="checkbox" id="selectAll"></th>
            <th>Name</th><th>Email</th><th>Phone</th><th>Plans</th><th>Wallet</th><th>Verified</th><th>Joined</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($customers as $c): ?>
            <tr>
              <td><input type="checkbox" name="customer_ids[]" value="<?= (int) $c['id'] ?>" class="customer-checkbox"></td>
              <td class="name-cell"><div class="avatar-sm"><?= e(strtoupper(substr($c['full_name'], 0, 1))) ?></div><b><?= e($c['full_name']) ?></b></td>
              <td><?= e($c['email']) ?></td>
              <td><?= e($c['phone'] ?: '—') ?></td>
              <td>
                <?php if ((int) $c['active_plans'] > 0): ?>
                  <span class="status-chip status-active"><?= (int) $c['active_plans'] ?> active</span>
                <?php elseif ((int) $c['total_plans'] > 0): ?>
                  <span class="status-chip status-expired">Expired/cancelled</span>
                <?php else: ?>
                  <span class="progress-tiny">No plan</span>
                <?php endif; ?>
              </td>
              <td><?= money($c['wallet_balance']) ?></td>
              <td><?= $c['email_verified_at'] ? '<span class="status-chip status-active">Verified</span>' : '<span class="status-chip status-temp">Unverified</span>' ?></td>
              <td><?= e(date('M j, Y', strtotime($c['created_at']))) ?></td>
              <td><a href="<?= url('/admin/customers/' . $c['id'] . '/cards') ?>" target="_blank" class="btn btn-outline btn-sm" style="text-decoration:none;">Cards</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="panel-box">
      <h5>Send email to selected customers <span class="progress-tiny" id="selectedCount">(0 selected)</span></h5>
      <div class="field" style="margin-bottom:14px;"><label>Subject</label><input type="text" name="subject" placeholder="e.g. New services just added!" required></div>
      <div class="field" style="margin-bottom:14px;"><label>Message</label><textarea name="message" placeholder="Write your message — line breaks are preserved." required></textarea></div>
      <button type="submit" class="btn btn-primary" id="sendEmailBtn" disabled>Send Email</button>
      <p class="progress-tiny" style="margin-top:8px;">Uses the same branded template as other King&rsquo;s Cut Saloon emails — subject and message are exactly what you type above.</p>
    </div>
  </form>
<?php endif; ?>

<script>
  const selectAll = document.getElementById('selectAll');
  const checkboxes = document.querySelectorAll('.customer-checkbox');
  const countLabel = document.getElementById('selectedCount');
  const sendBtn = document.getElementById('sendEmailBtn');

  function updateCount() {
    const checked = document.querySelectorAll('.customer-checkbox:checked').length;
    countLabel.textContent = '(' + checked + ' selected)';
    sendBtn.disabled = checked === 0;
  }

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
      updateCount();
    });
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
  }

  const bulkForm = document.getElementById('bulkEmailForm');
  if (bulkForm) {
    bulkForm.addEventListener('submit', (e) => {
      const checked = document.querySelectorAll('.customer-checkbox:checked').length;
      if (checked === 0) {
        e.preventDefault();
        alert('Select at least one customer first.');
      }
    });
  }
</script>
