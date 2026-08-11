<div class="shell">
  <section>
    <div class="section-head">
      <span class="eyebrow">Get In Touch</span>
      <h2>Come see the shop, or send word ahead</h2>
      <p>Questions about a plan, a booking, or a corporate account — the front desk reads every message.</p>
    </div>

    <div class="contact-grid">
      <form class="contact-form" method="post" action="<?= url('/contact') ?>">
        <?php csrf_field(); ?>
        <div class="row-2">
          <div class="field"><label for="c-name">Full name</label><input id="c-name" name="full_name" type="text" placeholder="Alex Morgan" required></div>
          <div class="field"><label for="c-phone">Phone</label><input id="c-phone" name="phone" type="tel" placeholder="+234 800 000 0000"></div>
        </div>
        <div class="field"><label for="c-email">Email</label><input id="c-email" name="email" type="email" placeholder="you@email.com" required></div>
        <div class="field">
          <label for="c-subject">What's this about?</label>
          <select id="c-subject" name="subject">
            <option>General enquiry</option>
            <option>Membership plans</option>
            <option>Corporate account</option>
            <option>Wallet or payment issue</option>
            <option>Feedback</option>
          </select>
        </div>
        <div class="field"><label for="c-msg">Message</label><textarea id="c-msg" name="message" placeholder="Tell us what you need..." required></textarea></div>
        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
      </form>

      <div class="shop-card">
        <h4>King&rsquo;s Cut Saloon</h4>
        <div class="shop-line">
          <svg class="glyph" viewBox="0 0 24 24" fill="none"><path d="M12 21C12 21 5 14.5 5 9.5C5 5.9 8.1 3 12 3C15.9 3 19 5.9 19 9.5C19 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.5"/></svg>
          <div><b>14 Ring Road</b><span>Benin City, Edo State, Nigeria</span></div>
        </div>
        <div class="shop-line">
          <svg class="glyph" viewBox="0 0 24 24" fill="none"><path d="M4 5C4 4 5 3 6 3H8L10 8L7.5 9.5C8.5 12 11 14.5 13.5 15.5L15 13L20 15V17C20 18 19 19 18 19C10 19 4 13 4 5Z" stroke="currentColor" stroke-width="1.5"/></svg>
          <div><b>+234 803 000 1147</b><span>Front desk &amp; bookings</span></div>
        </div>
        <div class="shop-line">
          <svg class="glyph" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M3 7L12 13L21 7" stroke="currentColor" stroke-width="1.5"/></svg>
          <div><b>hello@kingscutsaloon.com</b><span>General enquiries</span></div>
        </div>
        <div class="shop-line" style="border-bottom:none; flex-direction:column;">
          <div style="display:flex; gap:14px; width:100%;">
            <svg class="glyph" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M12 7V12L15 14" stroke="currentColor" stroke-width="1.5"/></svg>
            <b style="flex:1;">Opening hours</b>
          </div>
          <table class="hours-table">
            <tr><td>Monday – Friday</td><td>9:00 – 19:00</td></tr>
            <tr><td>Saturday</td><td>9:00 – 20:00</td></tr>
            <tr><td>Sunday</td><td>Closed</td></tr>
          </table>
        </div>
        <div class="map-block">MAP PREVIEW — 14 RING ROAD</div>
        <div class="social-row">
          <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>
          <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="none"><path d="M14 9H16.5V6H14C11.8 6 10 7.8 10 10V12H8V15H10V21H13V15H15.5L16 12H13V10C13 9.4 13.4 9 14 9Z" stroke="currentColor" stroke-width="1.3"/></svg></a>
          <a href="#" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="none"><path d="M4 20L5.4 15.8C4.5 14.3 4 12.6 4 10.9C4 5.9 8.5 2 13.5 2C18.5 2 22 5.9 22 10.9C22 15.9 18.5 19.8 13.5 19.8C11.9 19.8 10.4 19.4 9.1 18.6L4 20Z" stroke="currentColor" stroke-width="1.3"/></svg></a>
        </div>
      </div>
    </div>
  </section>
</div>
