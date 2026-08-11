    </main>
  </div>
</div>

<!-- ============ Shared QR / Share modal ============ -->
<div class="share-modal-overlay" id="shareModalOverlay">
  <div class="share-modal">
    <button type="button" class="share-modal-close" id="shareModalClose" aria-label="Close">&times;</button>

    <div class="share-modal-qr" id="shareModalQr"></div>

    <div class="share-modal-id">
      <span class="status-chip status-active" id="shareModalMeta" style="display:inline-block; margin-bottom:8px;"></span>
      <h4 id="shareModalLabel"></h4>
      <p class="mono" id="shareModalCode"></p>
    </div>

    <div class="share-modal-actions">
      <button type="button" class="btn btn-outline btn-sm" id="shareCopyBtn">Copy Link</button>
      <button type="button" class="btn btn-outline btn-sm" id="shareWhatsappBtn">WhatsApp</button>
      <button type="button" class="btn btn-outline btn-sm" id="shareTwitterBtn">X / Twitter</button>
      <button type="button" class="btn btn-outline btn-sm" id="shareFacebookBtn">Facebook</button>
      <button type="button" class="btn btn-outline btn-sm" id="shareNativeBtn" style="display:none;">Share…</button>
    </div>

    <input type="text" id="shareModalLink" readonly class="share-modal-link-input" onclick="this.select()">

    <form id="shareEmailForm" class="share-email-form">
      <label for="shareEmailInput">Or send it to a guest's email</label>
      <div class="share-email-row">
        <input type="email" id="shareEmailInput" placeholder="guest@email.com" required>
        <button type="submit" class="btn btn-primary btn-sm">Send</button>
      </div>
      <p class="share-email-status" id="shareEmailStatus"></p>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>

</body>
</html>
