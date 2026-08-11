<?php
/** @var array $pricing */
use App\Models\SessionPricing;
?>
<div class="dash-head">
  <div><h3>Booking Sessions</h3><p>Set the base fee and per-person rate for each session — changes go live on the Reserve page immediately.</p></div>
</div>

<form method="post" action="<?= url('/admin/sessions/update') ?>">
  <?php csrf_field(); ?>
  <div class="session-edit-grid">
    <?php foreach ($pricing as $row):
      $key = $row['session_type'] . '_' . $row['location_type'];
    ?>
      <div class="session-edit-card">
        <h5><?= e(SessionPricing::sessionLabel($row['session_type'])) ?></h5>
        <div class="loc"><?= e(SessionPricing::locationLabel($row['location_type'])) ?></div>

        <div class="edit-row">
          <div class="field"><label>Base price (₦)</label><input type="number" name="base[<?= e($key) ?>]" value="<?= (float) $row['base_price'] ?>" step="500" min="0"></div>
          <div class="field"><label>Per person (₦)</label><input type="number" name="per_person[<?= e($key) ?>]" value="<?= (float) $row['price_per_person'] ?>" step="500" min="0"></div>
        </div>
        <div class="checkbox-row">
          <input type="checkbox" id="active_<?= e($key) ?>" name="active[<?= e($key) ?>]" <?= $row['is_active'] ? 'checked' : '' ?>>
          <label for="active_<?= e($key) ?>">Available for booking</label>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="submit" class="btn btn-primary">Save All Session Pricing</button>
</form>
