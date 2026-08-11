<?php
/** @var array $items */
?>
<div class="dash-head">
  <div><h3>Our Work</h3><p>Upload photos or add a YouTube link — new items appear on the front page and the Our Work page right away.</p></div>
</div>

<div class="edit-grid">
  <div class="edit-card">
    <div class="edit-card-head"><h4>Upload an Image</h4></div>
    <form method="post" action="<?= url('/admin/work/image') ?>" enctype="multipart/form-data">
      <?php csrf_field(); ?>
      <div class="field" style="margin-bottom:12px;"><label>Title (optional)</label><input type="text" name="title" placeholder="e.g. Signature Fade — Before & After"></div>
      <div class="field" style="margin-bottom:12px;"><label>Image file (JPG, PNG, WEBP, or GIF — max 5MB)</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required></div>
      <div class="field" style="margin-bottom:14px;"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
      <button type="submit" class="btn btn-primary">Upload Image</button>
    </form>
  </div>

  <div class="edit-card">
    <div class="edit-card-head"><h4>Add a YouTube Video</h4></div>
    <form method="post" action="<?= url('/admin/work/video') ?>">
      <?php csrf_field(); ?>
      <div class="field" style="margin-bottom:12px;"><label>Title (optional)</label><input type="text" name="title" placeholder="e.g. A Look Inside the Chair"></div>
      <div class="field" style="margin-bottom:12px;"><label>YouTube link</label><input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required></div>
      <div class="field" style="margin-bottom:14px;"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
      <button type="submit" class="btn btn-primary">Add Video</button>
    </form>
  </div>
</div>

<h5 style="margin-bottom:6px;">Current gallery <span class="progress-tiny">(<?= count($items) ?> items)</span></h5>
<?php if (empty($items)): ?>
  <p class="empty-note">Nothing uploaded yet.</p>
<?php else: ?>
  <div class="work-admin-grid">
    <?php foreach ($items as $item): ?>
      <div class="work-admin-item">
        <?php if ($item['type'] === 'image'): ?>
          <img class="thumb" src="<?= e(url('assets/' . $item['image_path'])) ?>" alt="">
        <?php else: ?>
          <div class="thumb-video">▶</div>
        <?php endif; ?>
        <div class="meta">
          <b><?= e($item['title'] ?: ucfirst($item['type'])) ?></b>
          <span class="progress-tiny"><?= e(ucfirst($item['type'])) ?> · order <?= (int) $item['sort_order'] ?></span>
          <form method="post" action="<?= url('/admin/work/' . $item['id'] . '/delete') ?>" style="margin-top:8px;" onsubmit="return confirm('Remove this from the gallery?');">
            <?php csrf_field(); ?>
            <button type="submit" class="btn btn-outline btn-sm" style="font-size:11px; padding:5px 10px;">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
