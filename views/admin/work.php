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
<<<<<<< HEAD
        <?php if ($item['type'] === 'image'):
          $fullUrl = e(url('assets/' . $item['image_path']));
        ?>
          <img class="thumb" src="<?= $fullUrl ?>" alt="" style="cursor:pointer;"
               onclick="openWorkLightbox('image', <?= json_encode($fullUrl) ?>, <?= json_encode($item['title'] ?: '') ?>)">
        <?php else: ?>
          <div class="thumb-video" style="cursor:pointer;"
               onclick="openWorkLightbox('video', <?= json_encode($item['youtube_video_id']) ?>, <?= json_encode($item['title'] ?: '') ?>)">▶</div>
=======
        <?php if ($item['type'] === 'image'): ?>
          <img class="thumb" src="<?= e(url('assets/' . $item['image_path'])) ?>" alt="">
        <?php else: ?>
          <div class="thumb-video">▶</div>
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
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
<<<<<<< HEAD

<!-- ============ Work item lightbox ============ -->
<div class="share-modal-overlay" id="workLightboxOverlay">
  <div class="share-modal" style="max-width:640px;">
    <button type="button" class="share-modal-close" onclick="document.getElementById('workLightboxOverlay').classList.remove('open')" aria-label="Close">&times;</button>
    <div id="workLightboxBody" style="margin-top:10px;"></div>
    <p id="workLightboxTitle" style="text-align:center; margin-top:14px; color:var(--parchment-dim); font-size:13.5px;"></p>
  </div>
</div>

<script>
  function openWorkLightbox(type, value, title) {
    const body = document.getElementById('workLightboxBody');
    body.innerHTML = '';
    if (type === 'image') {
      const img = document.createElement('img');
      img.src = value;
      img.style.cssText = 'width:100%; border-radius:8px; display:block;';
      body.appendChild(img);
    } else {
      const iframe = document.createElement('iframe');
      iframe.src = 'https://www.youtube.com/embed/' + value + '?autoplay=1';
      iframe.style.cssText = 'width:100%; aspect-ratio:16/9; border:0; border-radius:8px; display:block;';
      iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
      iframe.allowFullscreen = true;
      body.appendChild(iframe);
    }
    document.getElementById('workLightboxTitle').textContent = title || '';
    document.getElementById('workLightboxOverlay').classList.add('open');
  }
  document.getElementById('workLightboxOverlay').addEventListener('click', function (e) {
    if (e.target === this) {
      this.classList.remove('open');
      document.getElementById('workLightboxBody').innerHTML = ''; // stop video playback
    }
  });
</script>
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
