<?php
/** @var array $plans */
$user = \App\Core\Auth::user();
?>
<div class="shell">
  <div class="hero">
    <div>
      <span class="eyebrow">Membership, Not Guesswork</span>
      <h1>The chair remembers <em>your</em> name.</h1>
      <p class="lede">One membership ticket unlocks every visit — wallet balance, attendance history, and a QR you flash at the door. No more paper punch cards, no more "who booked what."</p>
      <div class="hero-actions">
        <a href="<?= url('/membership') ?>" class="btn btn-primary" style="text-decoration:none;">View Membership Plans</a>
        <a href="<?= url('/services') ?>" class="btn btn-outline" style="text-decoration:none;">See the Chair Menu</a>
      </div>
      <div class="stat-row">
        <div class="stat"><b><?= count($plans) ?></b><span>Membership tiers</span></div>
        <div class="stat"><b>&lt; 5s</b><span>QR check-in time</span></div>
        <div class="stat"><b>0%</b><span>Wallet negative balance</span></div>
      </div>
    </div>

    <div class="hero-ticket-wrap">
      <div class="seal">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none"><path d="M12 2L14.5 8.5L21 9.3L16 13.9L17.4 20.5L12 17L6.6 20.5L8 13.9L3 9.3L9.5 8.5L12 2Z" stroke="#E7C579" stroke-width="1.4" stroke-linejoin="round"/></svg>
      </div>
      <div class="ticket">
        <div class="ticket-main">
          <div class="ticket-top-row">
            <div class="ticket-brand">
              <span class="crown-title mono">KC</span>
              <span class="eyebrow" style="margin:0;">Membership Ticket</span>
            </div>
            <span class="status-chip status-active">Active</span>
          </div>
          <div class="ticket-name"><?= $user ? e($user['full_name']) : 'Your Name Here' ?></div>
          <div class="ticket-id">KC-XXXX-XX</div>
          <div class="ticket-meta">
            <div><span>Plan</span><b>Choose below</b></div>
            <div><span>Expires</span><b>—</b></div>
            <div><span>Wallet</span><b>₦0</b></div>
          </div>
        </div>
        <div class="ticket-stub">
          <span class="stub-label">Scan to<br>check in</span>
          <div class="qr" aria-hidden="true">
            <i></i><i></i><i></i><i></i><i></i><i></i>
            <i></i><i></i><i></i><i></i><i></i><i></i>
            <i></i><i></i><i></i><i></i><i></i><i></i>
            <i></i><i></i><i></i><i></i><i></i><i></i>
            <i></i><i></i><i></i><i></i><i></i><i></i>
            <i></i><i></i><i></i><i></i><i></i><i></i>
          </div>
          <span class="stub-label">No. ——</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="shell section-tight">
  <div class="value-grid">
    <div class="value-item">
      <svg class="glyph" viewBox="0 0 24 24" fill="none"><path d="M3 10H21M6 6H18C19.1 6 20 6.9 20 8V17C20 18.1 19.1 19 18 19H6C4.9 19 4 18.1 4 17V8C4 6.9 4.9 6 6 6Z" stroke="currentColor" stroke-width="1.6"/></svg>
      <h4>Wallet, always ready</h4>
      <p>Top up once, pay for cuts and shaves from the balance — never negative, never a card at the counter.</p>
    </div>
    <div class="value-item">
      <svg class="glyph" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="1.6"/><rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="1.6"/><path d="M14 16H21M17.5 14V21" stroke="currentColor" stroke-width="1.6"/></svg>
      <h4>QR check-in</h4>
      <p>Flash your ticket's QR at the front desk. Active memberships mark attendance in a second flat.</p>
    </div>
    <div class="value-item">
      <svg class="glyph" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="9" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M3 20C3 16.5 5.7 14 9 14C12.3 14 15 16.5 15 20M15 20C15 17.3 16.8 15.5 19 15.5C20.5 15.5 21 16.5 21 20" stroke="currentColor" stroke-width="1.6"/></svg>
      <h4>Family &amp; guest IDs</h4>
      <p>Generate temporary or permanent secondary IDs for household members — revoke any of them, anytime.</p>
    </div>
    <div class="value-item">
      <svg class="glyph" viewBox="0 0 24 24" fill="none"><path d="M12 3V12L18 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/></svg>
      <h4>Full attendance history</h4>
      <p>Every visit logged and dated — see your streak, your busiest month, your last cut.</p>
    </div>
  </div>
</div>

<div class="shell section-tight">
  <div class="section-head">
    <span class="eyebrow">Pick Your Chair</span>
    <h2>Membership at a glance</h2>
    <p>Every plan carries its own Membership ID, QR code, and wallet discount on services.</p>
  </div>
  <div class="plan-mini-grid">
    <?php foreach ($plans as $plan): ?>
      <div class="plan-mini">
        <span class="eyebrow"><?= e($plan['tagline']) ?></span>
        <h4><?= e($plan['name']) ?></h4>
        <div class="price">
          <?php if ($plan['is_custom_pricing']): ?>
            Custom <span>quote</span>
          <?php else:
            $monthly = $plan['prices']['monthly'] ?? ['price' => 0, 'compare_at_price' => null];
            $onSale = $monthly['compare_at_price'] !== null && $monthly['compare_at_price'] > $monthly['price'];
          ?>
            <?php if ($onSale): ?><span class="compare-price"><?= money($monthly['compare_at_price']) ?></span><?php endif; ?>
            <?= money($monthly['price']) ?> <span>/ mo</span>
          <?php endif; ?>
        </div>
        <ul>
          <li><?= (int) $plan['max_secondary_ids'] > 0 ? (int) $plan['max_secondary_ids'] . ' secondary ID(s) included' : 'No secondary IDs' ?></li>
          <li>Wallet service discount</li>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!empty($workItems)): ?>
<div class="shell section-tight">
  <div class="section-head">
    <span class="eyebrow">Our Work</span>
    <h2>See the chair in action</h2>
    <p>A look at recent fades, sessions, and setups.</p>
  </div>
  <div class="work-preview-strip">
    <?php foreach ($workItems as $item): ?>
      <div class="work-item">
        <div class="work-media">
          <?php if ($item['type'] === 'image'): ?>
            <img src="<?= e(url('assets/' . $item['image_path'])) ?>" alt="<?= e($item['title'] ?? 'King\'s Cut Saloon work') ?>" loading="lazy">
          <?php else: ?>
            <iframe src="https://www.youtube.com/embed/<?= e($item['youtube_video_id']) ?>" title="<?= e($item['title'] ?? 'King\'s Cut Saloon video') ?>" allowfullscreen loading="lazy"></iframe>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div style="text-align:center; margin-top:26px;">
    <a href="<?= url('/work') ?>" class="btn btn-outline" style="text-decoration:none;">View All Our Work</a>
  </div>
</div>
<?php endif; ?>

<div class="shell section-tight">
  <div class="cta-banner">
    <h3>Ready to claim your chair? Your first ticket is one signup away.</h3>
    <a href="<?= url('/membership') ?>" class="btn btn-primary" style="text-decoration:none;">Get Membership</a>
  </div>
</div>

<footer>
  <div class="shell">
    <div class="foot-grid">
      <div>
        <div class="crown-title" style="font-size:16px;">KING&rsquo;S CUT SALOON</div>
        <p style="color:var(--parchment-dim); font-size:13px; margin-top:12px; max-width:260px;">A membership-run barbershop where every visit is tracked, every discount automatic, and every regular known by name.</p>
      </div>
      <div>
        <h5>Explore</h5>
        <ul>
          <li><a href="<?= url('/services') ?>">Services</a></li>
          <li><a href="<?= url('/membership') ?>">Membership</a></li>
          <li><a href="<?= url('/work') ?>">Our Work</a></li>
          <li><a href="<?= url('/reserve') ?>">Reserve</a></li>
          <li><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
        </ul>
      </div>
      <div>
        <h5>Shop</h5>
        <ul>
          <li>14 Ring Road, Benin City</li>
          <li>Mon–Sat, 9am–7pm</li>
          <li>+234 803 000 1147</li>
        </ul>
      </div>
      <div>
        <h5>Support</h5>
        <ul>
          <li><a href="<?= url('/contact') ?>">Contact us</a></li>
          <li>hello@kingscutsaloon.com</li>
        </ul>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© <?= date('Y') ?> King&rsquo;s Cut Saloon.</span>
      <span>Membership Management System — MVP</span>
    </div>
  </div>
</footer>
