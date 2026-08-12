<?php
/**
 * @var array|null $secondary   set when the token belongs to a secondary/guest ID
 * @var array|null $subscription set when the token belongs to a primary ticket
 */
$isSecondary = !empty($secondary);
$label = $isSecondary ? $secondary['label'] : 'Primary Member';
$code = $isSecondary ? $secondary['secondary_code'] : $subscription['membership_id'];
$planName = $isSecondary ? $secondary['plan_name'] : $subscription['plan_name'];
$status = $isSecondary ? $secondary['status'] : $subscription['status'];
$qrToken = $isSecondary ? $secondary['qr_token'] : $subscription['qr_token'];
$statusClass = $status === 'active' ? 'status-active' : ($status === 'expired' ? 'status-expired' : ($status === 'revoked' ? 'status-expired' : 'status-temp'));
?>
<h2>King&rsquo;s Cut Saloon</h2>
<p class="sub"><?= $isSecondary ? 'Secondary Membership ID' : 'Membership Ticket' ?></p>

<div style="display:flex; justify-content:center; margin-bottom:24px;">
  <div id="publicQr" style="background:var(--parchment); padding:14px; border-radius:10px;"></div>
</div>

<div style="text-align:center; margin-bottom:8px;">
  <span class="status-chip <?= e($statusClass) ?>"><?= e(ucfirst($status)) ?></span>
</div>
<h3 style="text-align:center; font-size:22px; margin-bottom:2px;"><?= e($label) ?></h3>
<p style="text-align:center; font-family:'JetBrains Mono',monospace; color:var(--brass-bright); margin:0 0 6px;"><?= e($code) ?></p>
<p style="text-align:center; color:var(--parchment-dim); font-size:13.5px; margin:0 0 24px;"><?= e($planName) ?> plan</p>

<p style="text-align:center; font-size:12.5px; color:var(--parchment-dim); line-height:1.6;">
  Show this screen or QR code at the King&rsquo;s Cut Saloon front desk to check in.<br>
  This link doesn&rsquo;t expose any wallet, contact, or account details.
</p>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  new QRCode(document.getElementById('publicQr'), {
    text: window.location.href,
    width: 200,
    height: 200,
    colorDark: '#1B1410',
    colorLight: '#F3E8D6',
  });
</script>
