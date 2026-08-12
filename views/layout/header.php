<?php
/** @var string $title */
$user = \App\Core\Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPath = rtrim($currentPath, '/') ?: '/';
function navClass(string $path, string $current): string
{
    return $path === $current ? 'active' : '';
}
?>
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
<body data-base-path="<?= e(rtrim(BASE_PATH, '/')) ?>">

<header class="site">
  <div class="nav-row">
    <a class="brand" href="<?= url('/') ?>" style="text-decoration:none; color:inherit;">
      <svg class="brand-mark crest" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8 20L12 10L18 17L24 8L30 17L36 10L40 20L38 20V32C38 34 36 36 34 36H14C12 36 10 34 10 32V20H8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M17 27L21 23L24 26L31 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="17" cy="27" r="1.6" fill="currentColor"/>
        <circle cx="31" cy="19" r="1.6" fill="currentColor"/>
      </svg>
      <div class="brand-text">
        <div class="crown-title">KING&rsquo;S CUT</div>
        <div class="crown-sub">Saloon &amp; Membership</div>
      </div>
    </a>
    <nav class="tabs" id="navTabs">
      <a href="<?= url('/') ?>" class="<?= navClass('/', $currentPath) ?>" style="text-decoration:none;"><button class="<?= navClass('/', $currentPath) ?>">Home</button></a>
      <a href="<?= url('/services') ?>" style="text-decoration:none;"><button class="<?= navClass('/services', $currentPath) ?>">Services</button></a>
      <a href="<?= url('/membership') ?>" style="text-decoration:none;"><button class="<?= navClass('/membership', $currentPath) ?>">Membership</button></a>
      <a href="<?= url('/work') ?>" style="text-decoration:none;"><button class="<?= navClass('/work', $currentPath) ?>">Our Work</button></a>
      <a href="<?= url('/reserve') ?>" style="text-decoration:none;"><button class="<?= navClass('/reserve', $currentPath) ?>">Reserve</button></a>
      <a href="<?= url('/contact') ?>" style="text-decoration:none;"><button class="<?= navClass('/contact', $currentPath) ?>">Contact</button></a>
      <?php if ($user): ?>
        <a href="<?= url('/dashboard') ?>" style="text-decoration:none;"><button class="<?= str_starts_with($currentPath, '/dashboard') ? 'active' : '' ?>">Dashboard</button></a>
      <?php endif; ?>
    </nav>
    <?php if ($user): ?>
      <a href="<?= url('/logout') ?>" class="nav-cta" style="text-decoration:none; display:inline-flex;">Sign Out</a>
    <?php else: ?>
      <a href="<?= url('/register') ?>" class="nav-cta" style="text-decoration:none; display:inline-flex;">Join Now</a>
    <?php endif; ?>
    <button class="hamburger" onclick="document.getElementById('navTabs').style.display = document.getElementById('navTabs').style.display==='flex' ? 'none':'flex'">☰</button>
  </div>
</header>

<?php if ($success = flash('success')): ?>
  <div class="shell section-tight" style="padding-bottom:0;"><div class="flash flash-success"><?= e($success) ?></div></div>
<?php endif; ?>
<?php if ($error = flash('error')): ?>
  <div class="shell section-tight" style="padding-bottom:0;"><div class="flash flash-error"><?= e($error) ?></div></div>
<?php endif; ?>
