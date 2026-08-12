<?php
/** @var array $items */
?>
<div class="shell">
  <section>
    <div class="section-head">
      <span class="eyebrow">Our Work</span>
      <h2>See the chair in action</h2>
      <p>Real fades, real setups, real sessions — a look at what King&rsquo;s Cut Saloon delivers.</p>
    </div>

    <?php if (empty($items)): ?>
      <p class="empty-note">No work has been posted yet — check back soon.</p>
    <?php else: ?>
      <div class="work-grid">
        <?php foreach ($items as $item): ?>
          <div class="work-item">
            <div class="work-media">
              <?php if ($item['type'] === 'image'): ?>
                <img src="<?= e(url('assets/' . $item['image_path'])) ?>" alt="<?= e($item['title'] ?? 'King\'s Cut Saloon work') ?>" loading="lazy">
              <?php else: ?>
                <iframe src="https://www.youtube.com/embed/<?= e($item['youtube_video_id']) ?>" title="<?= e($item['title'] ?? 'King\'s Cut Saloon video') ?>" allowfullscreen loading="lazy"></iframe>
              <?php endif; ?>
            </div>
            <?php if (!empty($item['title'])): ?>
              <div class="work-caption"><?= e($item['title']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
