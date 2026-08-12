<?php
/**
 * @var array $customer
 * @var array $subscription
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Membership Card — <?= e($customer['full_name']) ?> — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=Work+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1B1410; --panel:#241A13; --panel-2:#2E2018; --parchment:#F3E8D6; --parchment-dim:#C9B995;
    --brass:#C89B3C; --brass-bright:#E7C579; --burgundy:#7A2530; --sage:#6E9272; --line-strong: rgba(243,232,214,0.28);
  }
  *{ box-sizing:border-box; }
  body{
    margin:0; background:#0f0b08; color:var(--parchment); font-family:'Work Sans',sans-serif;
    display:flex; flex-direction:column; align-items:center; padding:40px 20px;
  }
  .toolbar{ margin-bottom:28px; display:flex; gap:12px; }
  .btn{ padding:12px 22px; border-radius:999px; font-weight:600; font-size:14px; border:1px solid transparent; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }
  .btn-primary{ background:var(--brass); color:var(--ink); }
  .btn-outline{ background:transparent; color:var(--parchment); border-color:var(--line-strong); }

  .card{
    width:400px; background:linear-gradient(155deg, var(--panel-2), var(--panel));
    border:1px solid var(--line-strong); border-radius:18px; padding:0; overflow:hidden;
    box-shadow:0 30px 70px -20px rgba(0,0,0,0.7);
  }
  .card-top{ padding:26px 26px 20px; }
  .brand-row{ display:flex; align-items:center; gap:10px; margin-bottom:22px; }
  .crest{ width:30px; height:30px; color:var(--brass); }
  .brand-text .t1{ font-family:'Fraunces',serif; font-weight:900; font-size:15px; }
  .brand-text .t2{ font-family:'JetBrains Mono',monospace; font-size:8.5px; letter-spacing:.22em; color:var(--brass); text-transform:uppercase; }
  .status-chip{ float:right; font-family:'JetBrains Mono',monospace; font-size:9.5px; letter-spacing:.1em; text-transform:uppercase; padding:4px 10px; border-radius:999px; border:1px solid var(--sage); color:var(--sage); background:rgba(110,146,114,.12); }
  .status-chip.expired{ border-color:var(--burgundy); color:#C96570; background:rgba(122,37,48,.14); }
  .member-name{ font-family:'Fraunces',serif; font-size:24px; font-weight:700; margin-top:8px; }
  .member-id{ font-family:'JetBrains Mono',monospace; color:var(--brass-bright); font-size:13px; margin-top:4px; }
  .meta-grid{ display:flex; gap:26px; margin-top:18px; }
  .meta-grid div span{ display:block; font-size:9.5px; text-transform:uppercase; letter-spacing:.07em; color:var(--parchment-dim); }
  .meta-grid div b{ font-weight:600; font-size:13px; }

  .perf{ position:relative; border-top:2px dashed var(--line-strong); margin:0 26px; }
  .perf::before, .perf::after{ content:''; position:absolute; top:-11px; width:22px; height:22px; border-radius:50%; background:#0f0b08; }
  .perf::before{ left:-37px; } .perf::after{ right:-37px; }

  .card-bottom{ padding:22px 26px 26px; display:flex; align-items:center; gap:18px; }
  .qr-box{ background:var(--parchment); border-radius:8px; padding:10px; width:104px; height:104px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .card-bottom .txt b{ display:block; font-size:12.5px; margin-bottom:3px; }
  .card-bottom .txt span{ font-size:10.5px; color:var(--parchment-dim); }

  @media print {
    body{ background:#fff; padding:0; align-items:flex-start; }
    .toolbar{ display:none; }
    .card{ box-shadow:none; border:1px solid #999; }
  }
</style>
</head>
<body>

<div class="toolbar">
  <button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
  <a href="<?= url('/admin/customers/' . $customer['id'] . '/cards') ?>" class="btn btn-outline">&larr; Back</a>
</div>

<div class="card" id="printCard">
  <div class="card-top">
    <span class="status-chip <?= $subscription['status'] !== 'active' ? 'expired' : '' ?>"><?= e(ucfirst($subscription['status'])) ?></span>
    <div class="brand-row">
      <svg class="crest" viewBox="0 0 48 48" fill="none"><path d="M8 20L12 10L18 17L24 8L30 17L36 10L40 20L38 20V32C38 34 36 36 34 36H14C12 36 10 34 10 32V20H8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M17 27L21 23L24 26L31 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <div class="brand-text"><div class="t1">KING&rsquo;S CUT</div><div class="t2">Saloon &amp; Membership</div></div>
    </div>
    <div class="member-name"><?= e($customer['full_name']) ?></div>
    <div class="member-id"><?= e($subscription['membership_id']) ?></div>
    <div class="meta-grid">
      <div><span>Plan</span><b><?= e($subscription['plan_name']) ?> — <?= e(ucfirst($subscription['duration'])) ?></b></div>
      <div><span>Expires</span><b><?= e(date('d M Y', strtotime($subscription['end_date']))) ?></b></div>
    </div>
  </div>
  <div class="perf"></div>
  <div class="card-bottom">
    <div class="qr-box" id="qrBox"></div>
    <div class="txt">
      <b>Scan to check in</b>
      <span>No. <?= e(str_pad((string) $subscription['id'], 6, '0', STR_PAD_LEFT)) ?></span>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  new QRCode(document.getElementById('qrBox'), {
    text: <?= json_encode(url('/id/' . $subscription['qr_token'])) ?>,
    width: 84,
    height: 84,
    colorDark: '#1B1410',
    colorLight: '#F3E8D6',
  });
</script>
</body>
</html>
