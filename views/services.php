<?php
/** @var array $categories */
?>
<div class="shell">
  <section>
    <div class="section-head">
      <span class="eyebrow">The Chair Menu</span>
      <h2>Services &amp; pricing</h2>
      <p>Every service below can be paid by card, transfer, or wallet. Pay by wallet while your membership is active and the member rate applies automatically.</p>
    </div>

    <?php foreach ($categories as $category): ?>
      <div class="svc-cat">
        <div class="svc-cat-head">
          <h3><?= e($category['name']) ?></h3>
          <span class="count mono"><?= str_pad((string) count($category['services']), 2, '0', STR_PAD_LEFT) ?> services</span>
        </div>
        <div class="svc-list">
          <?php foreach ($category['services'] as $service): ?>
            <div class="svc-row">
              <div class="name">
                <b><?= e($service['name']) ?></b>
                <p><?= e($service['description']) ?></p>
              </div>
              <div class="dur mono"><?= (int) $service['duration_minutes'] ?> min</div>
              <span class="tag">Member rate</span>
              <div class="price">
                <?php if (!empty($service['compare_at_price']) && (float) $service['compare_at_price'] > (float) $service['standard_price']): ?>
                  <span class="compare-price"><?= money($service['compare_at_price']) ?></span>
                <?php endif; ?>
                <?= money($service['standard_price']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="discount-note">
      <svg class="glyph" viewBox="0 0 24 24" fill="none"><path d="M12 2L14.5 8.5L21 9.3L16 13.9L17.4 20.5L12 17L6.6 20.5L8 13.9L3 9.3L9.5 8.5L12 2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
      <p><strong>How the member rate works:</strong> prices are shown at standard rate above. Pay from your wallet while an active subscription is running and your plan's discount is applied automatically at checkout — no code to enter. Subscription purchases themselves are never discounted.</p>
    </div>
  </section>
</div>
