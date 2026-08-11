<?php
/** @var array $plans */
/** @var array $durations */
$durationLabels = [
    'monthly' => 'Monthly',
    '3m'      => '3 Months',
    '6m'      => '6 Months',
    'yearly'  => 'Yearly',
];
$perLabels = [
    'monthly' => '/ month',
    '3m'      => '/ 3 months',
    '6m'      => '/ 6 months',
    'yearly'  => '/ year',
];
?>
<div class="shell">
  <section>
    <div class="section-head">
      <span class="eyebrow">Plans &amp; Duration</span>
      <h2>Find your membership</h2>
      <p>Every plan includes a Membership ID, a personal QR code, wallet access, and attendance tracking. Choose how long you're committing.</p>
    </div>

    <div class="duration-toggle" id="durationToggle">
      <?php foreach ($durations as $i => $d): ?>
        <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-dur="<?= e($d) ?>"><?= e($durationLabels[$d]) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="plan-grid">
      <?php foreach ($plans as $plan): ?>
        <div class="plan-card <?= $plan['is_featured'] ? 'featured' : '' ?>">
          <div class="plan-card-head">
            <?php if ($plan['is_featured']): ?><span class="featured-flag">Most Chosen</span><?php endif; ?>
            <h4><?= e($plan['name']) ?></h4>
            <p class="who"><?= e($plan['tagline']) ?></p>
          </div>
          <div class="plan-price">
            <?php if ($plan['is_custom_pricing']): ?>
              <span class="amt" data-plan="<?= e($plan['code']) ?>">Custom</span>
              <span class="per" data-perplan="<?= e($plan['code']) ?>">quote</span>
            <?php else:
              $initial = $plan['prices'][$durations[0]] ?? ['price' => 0, 'compare_at_price' => null];
              $hasSale = $initial['compare_at_price'] !== null && $initial['compare_at_price'] > $initial['price'];
            ?>
              <?php if ($hasSale): ?>
                <span class="compare-price" data-compareplan="<?= e($plan['code']) ?>"><?= money($initial['compare_at_price']) ?></span>
              <?php else: ?>
                <span class="compare-price" data-compareplan="<?= e($plan['code']) ?>" style="display:none;"></span>
              <?php endif; ?>
              <span class="amt" data-plan="<?= e($plan['code']) ?>"><?= money($initial['price']) ?></span>
              <span class="per" data-perplan="<?= e($plan['code']) ?>"><?= e($perLabels[$durations[0]]) ?></span>
            <?php endif; ?>
          </div>
          <ul class="plan-perks">
            <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2"/></svg>
              <?= (int) $plan['max_secondary_ids'] > 90 ? 'Bulk secondary IDs' : ((int) $plan['max_secondary_ids'] > 0 ? (int) $plan['max_secondary_ids'] . ' secondary ID(s) included' : 'No secondary IDs') ?>
            </li>
            <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2"/></svg>Wallet + <?= (float) $plan['discount_percent'] ?>% service discount</li>
            <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2"/></svg>Full attendance history</li>
          </ul>
          <div class="plan-card-foot">
            <?php if ($plan['is_custom_pricing']): ?>
              <a href="<?= url('/contact') ?>" class="btn btn-outline btn-block" style="text-decoration:none;">Talk to Us</a>
            <?php else: ?>
              <form method="post" action="<?= url('/membership/subscribe') ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                <input type="hidden" name="duration" class="duration-input" value="<?= e($durations[0]) ?>">
                <button type="submit" class="btn <?= $plan['is_featured'] ? 'btn-primary' : 'btn-outline' ?> btn-block">Choose <?= e($plan['name']) ?></button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="info-box">
      <svg class="glyph" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="9" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M3 20C3 16.5 5.7 14 9 14C12.3 14 15 16.5 15 20M15 20C15 17.3 16.8 15.5 19 15.5C20.5 15.5 21 16.5 21 20" stroke="currentColor" stroke-width="1.6"/></svg>
      <div>
        <h5>About secondary IDs</h5>
        <p>Secondary IDs inherit your plan's validity and discount, and can check in or log in exactly like your primary ID. They can't generate further IDs, touch wallet settings, or cancel your subscription — and you can revoke any of them the moment they're no longer needed, right from your dashboard.</p>
      </div>
    </div>
  </section>
</div>

<script>
  const planPrices = <?= json_encode(array_map(fn($p) => $p['is_custom_pricing'] ? null : $p['prices'], array_combine(array_column($plans, 'code'), $plans))) ?>;
  const perLabels = <?= json_encode($perLabels) ?>;

  document.querySelectorAll('#durationToggle button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#durationToggle button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const dur = btn.dataset.dur;

      document.querySelectorAll('[data-plan]').forEach(el => {
        const code = el.dataset.plan;
        if (planPrices[code] && planPrices[code][dur]) {
          el.textContent = '₦' + Number(planPrices[code][dur].price).toLocaleString();
        }
      });
      document.querySelectorAll('[data-compareplan]').forEach(el => {
        const code = el.dataset.compareplan;
        const entry = planPrices[code] && planPrices[code][dur];
        const compareAt = entry ? entry.compare_at_price : null;
        if (compareAt && compareAt > entry.price) {
          el.textContent = '₦' + Number(compareAt).toLocaleString();
          el.style.display = '';
        } else {
          el.textContent = '';
          el.style.display = 'none';
        }
      });
      document.querySelectorAll('[data-perplan]').forEach(el => {
        const code = el.dataset.perplan;
        if (planPrices[code]) {
          el.textContent = perLabels[dur];
        }
      });
      document.querySelectorAll('.duration-input').forEach(input => {
        input.value = dur;
      });
    });
  });
</script>
