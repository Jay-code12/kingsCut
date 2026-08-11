<?php
/** @var array $categories */
?>
<div class="dash-head">
  <div><h3>Services &amp; Categories</h3><p>Edit the chair menu — prices and strike-through sale prices go live on the Services page immediately.</p></div>
  <div style="display:flex; gap:10px;">
    <button class="btn btn-outline btn-sm" onclick="document.getElementById('addCategoryForm').classList.toggle('show')">+ Category</button>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('addServiceForm').classList.toggle('show')">+ Service</button>
  </div>
</div>

<div class="category-tab-row">
  <?php foreach ($categories as $cat): ?>
    <div class="category-tab">
      <span><?= e($cat['name']) ?></span>
      <span class="count-badge">(<?= count($cat['services']) ?>)</span>
      <form method="post" action="<?= url('/admin/services/categories/' . $cat['id'] . '/delete') ?>" onsubmit="return confirm('Remove this category? It must have no services in it.');">
        <?php csrf_field(); ?>
        <button type="submit" title="Delete category">&times;</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<form id="addCategoryForm" class="add-form" method="post" action="<?= url('/admin/services/categories/create') ?>">
  <?php csrf_field(); ?>
  <div class="edit-row">
    <div class="field"><label>Category name</label><input type="text" name="name" placeholder="e.g. Coloring" required></div>
    <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
  </div>
  <button type="submit" class="btn btn-primary">Add Category</button>
</form>

<form id="addServiceForm" class="add-form" method="post" action="<?= url('/admin/services/create') ?>">
  <?php csrf_field(); ?>
  <div class="edit-row three">
    <div class="field"><label>Service name</label><input type="text" name="name" placeholder="e.g. Hair Coloring" required></div>
    <div class="field">
      <label>Category</label>
      <select name="category_id" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int) $cat['id'] ?>"><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>Duration (minutes)</label><input type="number" name="duration_minutes" value="30" min="5" step="5"></div>
  </div>
  <div class="edit-row three">
    <div class="field"><label>Price (₦)</label><input type="number" name="standard_price" step="1" min="0" required></div>
    <div class="field"><label>Strike price (optional, ₦)</label><input type="number" name="compare_at_price" step="1" min="0" placeholder="Only if on sale"></div>
    <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
  </div>
  <div class="field" style="margin-bottom:14px;"><label>Description</label><input type="text" name="description" placeholder="Short description shown on the Services page"></div>
  <button type="submit" class="btn btn-primary">Add Service</button>
</form>

<?php foreach ($categories as $cat): ?>
  <div class="panel-box" style="margin-bottom:18px;">
    <h5><?= e($cat['name']) ?> <span class="progress-tiny">(<?= count($cat['services']) ?> services)</span></h5>
    <?php if (empty($cat['services'])): ?>
      <p class="empty-note">No services in this category yet.</p>
    <?php else: ?>
      <?php foreach ($cat['services'] as $svc): ?>
        <form method="post" action="<?= url('/admin/services/' . $svc['id'] . '/update') ?>" class="svc-admin-row">
          <?php csrf_field(); ?>
          <div class="field"><label>Name</label><input type="text" name="name" value="<?= e($svc['name']) ?>" required></div>
          <div class="field">
            <label>Category</label>
            <select name="category_id">
              <?php foreach ($categories as $c2): ?>
                <option value="<?= (int) $c2['id'] ?>" <?= (int) $c2['id'] === (int) $svc['category_id'] ? 'selected' : '' ?>><?= e($c2['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label>Price (₦)</label><input type="number" name="standard_price" value="<?= (float) $svc['standard_price'] ?>" step="1" min="0" required></div>
          <div class="field"><label>Strike price (₦)</label><input type="number" name="compare_at_price" value="<?= $svc['compare_at_price'] !== null ? (float) $svc['compare_at_price'] : '' ?>" step="1" min="0" placeholder="—"></div>
          <div style="display:flex; gap:8px; align-items:flex-end; height:100%;">
            <button type="submit" class="btn btn-outline btn-sm">Save</button>
          </div>
          <input type="hidden" name="duration_minutes" value="<?= (int) $svc['duration_minutes'] ?>">
          <input type="hidden" name="description" value="<?= e($svc['description'] ?? '') ?>">
          <input type="hidden" name="sort_order" value="<?= (int) $svc['sort_order'] ?>">
        </form>
        <div style="text-align:right; margin:-6px 0 6px;">
          <form method="post" action="<?= url('/admin/services/' . $svc['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Remove this service?');">
            <?php csrf_field(); ?>
            <button type="submit" class="btn btn-outline btn-sm" style="font-size:11px; padding:5px 10px;">Delete service</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
