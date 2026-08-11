<?php
/**
 * @var string $title
 * @var array $admin
 * @var string $activeNav
 */
use App\Core\AdminAuth;
$initials = AdminAuth::initials($admin['full_name']);
$isSuper = $admin['role'] === 'super_admin';
function adminNav(string $key, string $active): string
{
    return $key === $active ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> — Admin — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700;9..144,900&family=Work+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<header class="site">
  <div class="nav-row">
    <a class="brand" href="<?= url('/admin') ?>" style="text-decoration:none; color:inherit;">
      <svg class="brand-mark crest" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8 20L12 10L18 17L24 8L30 17L36 10L40 20L38 20V32C38 34 36 36 34 36H14C12 36 10 34 10 32V20H8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M17 27L21 23L24 26L31 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <div class="brand-text">
        <div class="crown-title">KING&rsquo;S CUT</div>
        <div class="crown-sub">Admin Console</div>
      </div>
    </a>
    <div style="display:flex; align-items:center; gap:16px;">
      <span class="admin-role-badge <?= $isSuper ? 'super' : 'admin' ?>"><?= $isSuper ? 'Super Admin' : 'Admin' ?></span>
      <span style="font-size:13px; color:var(--parchment-dim);"><?= e($admin['full_name']) ?></span>
      <a href="<?= url('/admin/logout') ?>" class="nav-cta" style="text-decoration:none; display:inline-flex;">Sign Out</a>
    </div>
  </div>
</header>

<div class="shell section-tight">
  <?php if ($success = flash('success')): ?>
    <div class="flash flash-success"><?= e($success) ?></div>
  <?php endif; ?>
  <?php if ($error = flash('error')): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="dash-shell">
    <aside class="dash-side">
      <div class="dash-profile">
        <div class="avatar"><?= e($initials) ?></div>
        <div>
          <b><?= e($admin['full_name']) ?></b>
          <span><?= e($admin['email']) ?></span>
        </div>
      </div>
      <nav class="dash-nav">
        <div class="nav-group-label">Operations</div>
        <a href="<?= url('/admin') ?>" style="text-decoration:none;">
          <button class="<?= adminNav('dashboard', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><path d="M4 20V10M11 20V4M18 20V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            Sales Overview
          </button>
        </a>
        <a href="<?= url('/admin/reservations') ?>" style="text-decoration:none;">
          <button class="<?= adminNav('reservations', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="15" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9H21M8 3V6M16 3V6" stroke="currentColor" stroke-width="1.6"/></svg>
            Reservations
          </button>
        </a>
        <a href="<?= url('/admin/work') ?>" style="text-decoration:none;">
          <button class="<?= adminNav('work', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><circle cx="8.5" cy="9.5" r="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M3 16L8 12L12 15L16 11L21 15" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            Our Work
          </button>
        </a>
        <?php if ($isSuper): ?>
          <div class="nav-group-label">Super Admin</div>
          <a href="<?= url('/admin/plans') ?>" style="text-decoration:none;">
            <button class="<?= adminNav('plans', $activeNav) ?>">
              <svg viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              Membership Plans
            </button>
          </a>
          <a href="<?= url('/admin/services') ?>" style="text-decoration:none;">
            <button class="<?= adminNav('services', $activeNav) ?>">
              <svg viewBox="0 0 24 24" fill="none"><path d="M6 3V9L3 21H21L18 9V3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M4.5 15H19.5" stroke="currentColor" stroke-width="1.6"/></svg>
              Services &amp; Categories
            </button>
          </a>
          <a href="<?= url('/admin/sessions') ?>" style="text-decoration:none;">
            <button class="<?= adminNav('sessions', $activeNav) ?>">
              <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7V12L15 14" stroke="currentColor" stroke-width="1.6"/></svg>
              Booking Sessions
            </button>
          </a>
        <?php endif; ?>
      </nav>
      <div class="dash-side-foot">
        Signed in as <?= $isSuper ? 'Super Admin' : 'Admin' ?><br>
        King&rsquo;s Cut Saloon
      </div>
    </aside>

    <main class="dash-main">
