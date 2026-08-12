<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700;9..144,900&family=Work+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="auth-shell">
  <div class="auth-card">
    <div class="brand-row">
      <svg width="30" height="30" class="crest" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8 20L12 10L18 17L24 8L30 17L36 10L40 20L38 20V32C38 34 36 36 34 36H14C12 36 10 34 10 32V20H8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M17 27L21 23L24 26L31 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <div>
        <div class="crown-title" style="font-size:16px;">KING&rsquo;S CUT</div>
        <div class="eyebrow" style="margin:0;">Saloon &amp; Membership</div>
      </div>
    </div>

    <?php if ($success = flash('success')): ?>
      <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error = flash('error')): ?>
      <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>
