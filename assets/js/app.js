/**
 * King's Cut Saloon — dashboard QR / share modal.
 * Shared by the Overview (primary ticket) and Family & Guest IDs (secondary
 * ID) pages. Requires the qrcodejs library (loaded via CDN) and a
 * `#shareModal` markup block (see views/layout/dashboard_footer.php).
 */
(function () {
  let qrInstance = null;
  let currentShareUrl = '';
  let currentEndpoint = '';
  let currentCsrfToken = '';

  function byId(id) { return document.getElementById(id); }

  window.openShareModal = function (opts) {
    // opts: { label, code, planName, status, qrToken, shareEndpoint, csrfToken }
    currentEndpoint = opts.shareEndpoint;
    currentCsrfToken = opts.csrfToken;

    const basePath = document.body.getAttribute('data-base-path') || '';
    currentShareUrl = window.location.origin + basePath + '/id/' + opts.qrToken;

    byId('shareModalLabel').textContent = opts.label;
    byId('shareModalCode').textContent = opts.code;
    byId('shareModalMeta').textContent = (opts.planName || '') + (opts.status ? ' · ' + opts.status : '');
    byId('shareModalLink').value = currentShareUrl;
    byId('shareEmailStatus').textContent = '';
    byId('shareEmailInput').value = '';

    const qrBox = byId('shareModalQr');
    qrBox.innerHTML = '';
    qrInstance = new QRCode(qrBox, {
      text: currentShareUrl,
      width: 176,
      height: 176,
      colorDark: '#1B1410',
      colorLight: '#F3E8D6',
    });

    byId('shareModalOverlay').classList.add('open');
  };

  window.closeShareModal = function () {
    byId('shareModalOverlay').classList.remove('open');
  };

  function logChannel(channel, recipient) {
    const body = new URLSearchParams();
    body.set('csrf_token', currentCsrfToken);
    body.set('channel', channel);
    if (recipient) body.set('recipient_email', recipient);
    return fetch(currentEndpoint, { method: 'POST', body }).then(r => r.json()).catch(() => null);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const overlay = byId('shareModalOverlay');
    if (!overlay) return; // modal not present on this page

    byId('shareModalClose').addEventListener('click', window.closeShareModal);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) window.closeShareModal();
    });

    byId('shareCopyBtn').addEventListener('click', function () {
      navigator.clipboard.writeText(currentShareUrl).then(() => {
        const btn = byId('shareCopyBtn');
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = original; }, 1500);
      });
      logChannel('copy_link');
    });

    byId('shareWhatsappBtn').addEventListener('click', function () {
      const text = encodeURIComponent('Here\u2019s my King\u2019s Cut Saloon ID: ' + currentShareUrl);
      window.open('https://wa.me/?text=' + text, '_blank');
      logChannel('whatsapp');
    });

    byId('shareTwitterBtn').addEventListener('click', function () {
      const text = encodeURIComponent('My King\u2019s Cut Saloon membership ID');
      window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + encodeURIComponent(currentShareUrl), '_blank');
      logChannel('twitter');
    });

    byId('shareFacebookBtn').addEventListener('click', function () {
      window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(currentShareUrl), '_blank');
      logChannel('facebook');
    });

    const nativeBtn = byId('shareNativeBtn');
    if (navigator.share) {
      nativeBtn.style.display = '';
      nativeBtn.addEventListener('click', function () {
        navigator.share({ title: "King's Cut Saloon ID", url: currentShareUrl })
          .then(() => logChannel('native'))
          .catch(() => {});
      });
    } else {
      nativeBtn.style.display = 'none';
    }

    byId('shareEmailForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const email = byId('shareEmailInput').value.trim();
      const statusEl = byId('shareEmailStatus');
      if (!email) return;
      statusEl.textContent = 'Sending…';
      statusEl.style.color = 'var(--parchment-dim)';
      logChannel('email', email).then(function (res) {
        if (res && res.success) {
          statusEl.style.color = 'var(--sage)';
          statusEl.textContent = res.message || 'Sent.';
        } else {
          statusEl.style.color = 'var(--burgundy-bright)';
          statusEl.textContent = (res && res.message) || 'Could not send. Try again.';
        }
      });
    });
  });
})();
