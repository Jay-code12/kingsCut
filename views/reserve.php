<?php
/**
 * @var array $categories
 * @var array $sessionOptions
 * @var array|null $user
 */
use App\Models\SessionPricing;
?>
<div class="shell">
  <section>
    <div class="section-head">
      <span class="eyebrow">Private Sessions</span>
      <h2>Book a session</h2>
      <p>VIP grooming for a group — at our office or wherever you need us. Tell us the details and we'll confirm by phone or email.</p>
    </div>

    <form method="post" action="<?= url('/reserve') ?>" class="reserve-form">
      <?php csrf_field(); ?>

      <div class="reserve-section">
        <h4>1. Choose your session</h4>
        <div class="session-grid">
          <?php foreach ($sessionOptions as $opt): ?>
            <label class="session-option">
              <input type="radio" name="session_location" value="<?= e($opt['session_type']) ?>|<?= e($opt['location_type']) ?>"
                     data-session="<?= e($opt['session_type']) ?>" data-location="<?= e($opt['location_type']) ?>"
                     data-base="<?= (float) $opt['base_price'] ?>" data-perperson="<?= (float) $opt['price_per_person'] ?>" required>
              <span class="session-option-body">
                <b><?= e(SessionPricing::sessionLabel($opt['session_type'])) ?></b>
                <span class="session-option-loc"><?= e(SessionPricing::locationLabel($opt['location_type'])) ?></span>
                <span class="session-option-price"><?= money($opt['base_price']) ?> base + <?= money($opt['price_per_person']) ?>/person</span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="session_type" id="sessionTypeInput">
        <input type="hidden" name="location_type" id="locationTypeInput">
      </div>

      <div class="reserve-section">
        <h4>2. Details</h4>
        <div class="row-2">
          <div class="field"><label for="number_of_people">Number of people</label><input type="number" id="number_of_people" name="number_of_people" min="1" value="1" required></div>
          <div class="field"><label for="reservation_date">Date</label><input type="date" id="reservation_date" name="reservation_date" min="<?= e(date('Y-m-d')) ?>" required></div>
        </div>
      </div>

      <div class="reserve-section">
        <h4>3. Services needed</h4>
        <p class="progress-tiny" style="margin-bottom:14px;">Select all that apply — priced at standard rate for a reservation.</p>
        <?php foreach ($categories as $cat): ?>
          <div class="svc-check-group">
            <span class="svc-check-cat"><?= e($cat['name']) ?></span>
            <div class="svc-check-grid">
              <?php foreach ($cat['services'] as $svc): ?>
                <label class="svc-check">
                  <input type="checkbox" name="services[]" value="<?= (int) $svc['id'] ?>" data-price="<?= (float) $svc['standard_price'] ?>">
                  <span><?= e($svc['name']) ?></span>
                  <span class="svc-check-price"><?= money($svc['standard_price']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="reserve-section">
<<<<<<< HEAD
        <h4>4. Got a discount? <span class="progress-tiny">(both optional)</span></h4>
        <div class="row-2">
          <div class="field">
            <label for="membership_id">Membership ID</label>
            <input type="text" id="membership_id" name="membership_id" placeholder="e.g. KC-0417-SG">
            <p class="discount-status" id="membershipStatus"></p>
          </div>
          <div class="field">
            <label for="coupon_code">Coupon code</label>
            <input type="text" id="coupon_code" name="coupon_code" placeholder="e.g. WELCOME10">
            <p class="discount-status" id="couponStatus"></p>
          </div>
        </div>
      </div>

      <div class="reserve-section">
        <h4>5. Your contact info</h4>
=======
        <h4>4. Your contact info</h4>
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
        <div class="row-2">
          <div class="field"><label for="full_name">Full name</label><input type="text" id="full_name" name="full_name" placeholder="Alex Morgan" value="<?= e($user['full_name'] ?? '') ?>" required></div>
          <div class="field"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" placeholder="+234 800 000 0000" value="<?= e($user['phone'] ?? '') ?>" required></div>
        </div>
        <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" placeholder="you@email.com" value="<?= e($user['email'] ?? '') ?>" required></div>
        <div class="field"><label for="notes">Anything we should know? (optional)</label><textarea id="notes" name="notes" placeholder="Occasion, preferred barbers, access instructions for VIP Outside, etc."></textarea></div>
      </div>

      <div class="reserve-summary" id="reserveSummary">
        <div class="reserve-summary-row"><span>Session</span><span id="sumSession">—</span></div>
        <div class="reserve-summary-row"><span>Services</span><span id="sumServices">₦0</span></div>
<<<<<<< HEAD
        <div class="reserve-summary-row" id="sumMembershipRow" style="display:none; color:var(--sage);"><span>Membership discount</span><span id="sumMembership">–₦0</span></div>
        <div class="reserve-summary-row" id="sumCouponRow" style="display:none; color:var(--sage);"><span>Coupon discount</span><span id="sumCoupon">–₦0</span></div>
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
        <div class="reserve-summary-row total"><span>Estimated total</span><span id="sumTotal">₦0</span></div>
        <p class="progress-tiny">Final pricing is confirmed by the front desk — this is an estimate.</p>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Request This Session</button>
    </form>
  </section>
</div>

<script>
<<<<<<< HEAD
  const basePath = document.body.getAttribute('data-base-path') || '';
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
  const peopleInput = document.getElementById('number_of_people');
  const sessionInputs = document.querySelectorAll('input[name="session_location"]');
  const serviceInputs = document.querySelectorAll('input[name="services[]"]');
  const sessionTypeInput = document.getElementById('sessionTypeInput');
  const locationTypeInput = document.getElementById('locationTypeInput');
<<<<<<< HEAD
  const membershipInput = document.getElementById('membership_id');
  const couponInput = document.getElementById('coupon_code');

  let membershipDiscountPercent = 0;
  let couponDiscountPercent = 0;
  let membershipTimer, couponTimer;

  function debounceCheck(input, timerRef, statusEl, endpoint, paramName, onResult) {
    clearTimeout(timerRef);
    const value = input.value.trim();
    const statusNode = document.getElementById(statusEl);
    if (value === '') {
      statusNode.textContent = '';
      statusNode.className = 'discount-status';
      onResult(0);
      recalc();
      return;
    }
    return setTimeout(() => {
      fetch(basePath + endpoint + '?' + paramName + '=' + encodeURIComponent(value))
        .then(r => r.json())
        .then(data => {
          if (data.valid) {
            statusNode.textContent = data.plan_name
              ? '✓ ' + data.plan_name + ' member — ' + data.discount_percent + '% off services'
              : '✓ Coupon applied — ' + data.discount_percent + '% off';
            statusNode.className = 'discount-status ok';
            onResult(data.discount_percent);
          } else {
            statusNode.textContent = data.message || 'Not valid.';
            statusNode.className = 'discount-status bad';
            onResult(0);
          }
          recalc();
        })
        .catch(() => { onResult(0); });
    }, 450);
  }

  membershipInput.addEventListener('input', () => {
    membershipTimer = debounceCheck(membershipInput, membershipTimer, 'membershipStatus', '/reserve/check-membership', 'membership_id', (pct) => { membershipDiscountPercent = pct; });
  });
  couponInput.addEventListener('input', () => {
    couponTimer = debounceCheck(couponInput, couponTimer, 'couponStatus', '/reserve/check-coupon', 'code', (pct) => { couponDiscountPercent = pct; });
  });
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1

  function recalc() {
    let base = 0, perPerson = 0, sessionLabel = '—';
    const checked = document.querySelector('input[name="session_location"]:checked');
    if (checked) {
      base = parseFloat(checked.dataset.base);
      perPerson = parseFloat(checked.dataset.perperson);
      sessionTypeInput.value = checked.dataset.session;
      locationTypeInput.value = checked.dataset.location;
      sessionLabel = checked.closest('.session-option').querySelector('b').textContent + ' — ' + checked.closest('.session-option').querySelector('.session-option-loc').textContent;
    }
    const people = Math.max(1, parseInt(peopleInput.value || '1', 10));
    const sessionTotal = base + (perPerson * people);

    let servicesTotal = 0;
    serviceInputs.forEach(input => { if (input.checked) servicesTotal += parseFloat(input.dataset.price); });

<<<<<<< HEAD
    const membershipDiscount = Math.round(servicesTotal * (membershipDiscountPercent / 100) * 100) / 100;
    const subtotalBeforeCoupon = sessionTotal + (servicesTotal - membershipDiscount);
    const couponDiscount = Math.round(subtotalBeforeCoupon * (couponDiscountPercent / 100) * 100) / 100;
    const total = subtotalBeforeCoupon - couponDiscount;

    document.getElementById('sumSession').textContent = sessionLabel + ' (' + people + ' ppl)';
    document.getElementById('sumServices').textContent = '₦' + servicesTotal.toLocaleString();

    const memRow = document.getElementById('sumMembershipRow');
    if (membershipDiscount > 0) {
      memRow.style.display = '';
      document.getElementById('sumMembership').textContent = '–₦' + membershipDiscount.toLocaleString();
    } else {
      memRow.style.display = 'none';
    }

    const couponRow = document.getElementById('sumCouponRow');
    if (couponDiscount > 0) {
      couponRow.style.display = '';
      document.getElementById('sumCoupon').textContent = '–₦' + couponDiscount.toLocaleString();
    } else {
      couponRow.style.display = 'none';
    }

    document.getElementById('sumTotal').textContent = '₦' + total.toLocaleString();
=======
    document.getElementById('sumSession').textContent = sessionLabel + ' (' + people + ' ppl)';
    document.getElementById('sumServices').textContent = '₦' + servicesTotal.toLocaleString();
    document.getElementById('sumTotal').textContent = '₦' + (sessionTotal + servicesTotal).toLocaleString();
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
  }

  sessionInputs.forEach(el => el.addEventListener('change', recalc));
  serviceInputs.forEach(el => el.addEventListener('change', recalc));
  peopleInput.addEventListener('input', recalc);
</script>
