<?php
/**
 * @var array $plans
 * @var array $durations
 */
$durationLabels = [
    'monthly' => 'Monthly',
    '3m'      => '3 Months',
    '6m'      => '6 Months',
    'yearly'  => 'Yearly',
];
?>
<div class="dash-head">
  <div><h3>Membership Plans</h3><p>Edit names, pricing, and strike-through sale prices — changes go live on the Membership page immediately.</p></div>
  <button class="btn btn-primary btn-sm" onclick="document.getElementById('addPlanForm').classList.toggle('show')">+ Add New Plan</button>
</div>

<form id="addPlanForm" class="add-form" method="post" action="<?= url('/admin/plans/create') ?>">
  <?php csrf_field(); ?>
  <div class="edit-row three">
    <div class="field"><label>Name</label><input type="text" name="name" placeholder="e.g. Student" required></div>
    <div class="field"><label>Short code (letters/numbers)</label><input type="text" name="code" placeholder="e.g. student" required></div>
    <div class="field"><label>Tagline</label><input type="text" name="tagline" placeholder="e.g. For campus regulars"></div>
  </div>
  <div class="edit-row three">
    <div class="field"><label>Max secondary IDs</label><input type="number" name="max_secondary_ids" value="0" min="0"></div>
    <div class="field"><label>Service discount %</label><input type="number" name="discount_percent" value="0" step="0.5" min="0" max="100"></div>
    <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
  </div>
  <div class="checkbox-row">
    <input type="checkbox" id="newFeatured" name="is_featured"> <label for="newFeatured">Mark as "Most Chosen"</label>
  </div>
  <div class="checkbox-row">
    <input type="checkbox" id="newCustom" name="is_custom_pricing"> <label for="newCustom">Custom / quote-only pricing (like Corporate)</label>
  </div>
  <button type="submit" class="btn btn-primary">Create Plan</button>
  <p class="progress-tiny" style="margin-top:10px;">New plans start with ₦0 pricing for every duration — set real prices below after creating it.</p>
</form>

<div class="edit-grid">
  <?php foreach ($plans as $plan): ?>
    <div class="edit-card <?= $plan['is_featured'] ? 'featured' : '' ?>">
      <form method="post" action="<?= url('/admin/plans/' . $plan['id'] . '/update') ?>">
        <?php csrf_field(); ?>
        <div class="edit-card-head">
          <h4><?= e($plan['name']) ?></h4>
          <span class="code-badge"><?= e($plan['code']) ?></span>
        </div>

        <div class="edit-row">
          <div class="field"><label>Name</label><input type="text" name="name" value="<?= e($plan['name']) ?>" required></div>
          <div class="field"><label>Tagline</label><input type="text" name="tagline" value="<?= e($plan['tagline'] ?? '') ?>"></div>
        </div>
        <div class="edit-row three">
          <div class="field"><label>Max secondary IDs</label><input type="number" name="max_secondary_ids" value="<?= (int) $plan['max_secondary_ids'] ?>" min="0"></div>
          <div class="field"><label>Discount %</label><input type="number" name="discount_percent" value="<?= (float) $plan['discount_percent'] ?>" step="0.5" min="0" max="100"></div>
          <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="<?= (int) $plan['sort_order'] ?>"></div>
        </div>
        <div class="checkbox-row">
          <input type="checkbox" id="featured<?= (int) $plan['id'] ?>" name="is_featured" <?= $plan['is_featured'] ? 'checked' : '' ?>>
          <label for="featured<?= (int) $plan['id'] ?>">Mark as "Most Chosen"</label>
        </div>
        <div class="checkbox-row">
          <input type="checkbox" id="custom<?= (int) $plan['id'] ?>" name="is_custom_pricing" <?= $plan['is_custom_pricing'] ? 'checked' : '' ?>>
          <label for="custom<?= (int) $plan['id'] ?>">Custom / quote-only pricing</label>
        </div>

        <?php if (!$plan['is_custom_pricing']): ?>
          <p class="progress-tiny" style="margin-bottom:8px;">Price per duration — set a strike-through price only if it's above the real price (i.e. an actual discount).</p>
          <div class="price-duration-grid">
            <?php foreach ($durations as $d): $p = $plan['prices'][$d] ?? ['price' => 0, 'compare_at_price' => null]; ?>
              <div class="price-duration-cell">
                <span class="dur-label"><?= e($durationLabels[$d]) ?></span>
                <input type="number" name="price[<?= e($d) ?>]" value="<?= (float) $p['price'] ?>" step="1" min="0" placeholder="Price">
                <input type="number" class="compare-input" name="compare_at_price[<?= e($d) ?>]" value="<?= $p['compare_at_price'] !== null ? (float) $p['compare_at_price'] : '' ?>" step="1" min="0" placeholder="Strike price (optional)">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-outline btn-block">Save Changes</button>
      </form>
      <form method="post" action="<?= url('/admin/plans/' . $plan['id'] . '/delete') ?>" style="margin-top:10px;" onsubmit="return confirm('Delete the &quot;<?= e(addslashes($plan['name'])) ?>&quot; plan? This can\'t be undone, and only works if no customer has ever subscribed to it.');">
        <?php csrf_field(); ?>
        <button type="submit" class="btn btn-outline btn-block" style="border-color:var(--burgundy); color:var(--burgundy-bright);">Delete Plan</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>
