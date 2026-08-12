<?php
/**
 * @var string $title
 * @var array $customer
 * @var string $activeNav
 */
$initials = \App\Core\Auth::initials($customer['full_name']);
function dnav(string $key, string $active): string
{
    return $key === $active ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> — Dashboard — <?= e(APP_NAME) ?></title>
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
      </svg>
      <div class="brand-text">
        <div class="crown-title">KING&rsquo;S CUT</div>
        <div class="crown-sub">Saloon &amp; Membership</div>
      </div>
    </a>
    <nav class="tabs">
      <a href="<?= url('/') ?>" style="text-decoration:none;"><button>Home</button></a>
      <a href="<?= url('/services') ?>" style="text-decoration:none;"><button>Services</button></a>
      <a href="<?= url('/membership') ?>" style="text-decoration:none;"><button>Membership</button></a>
      <a href="<?= url('/work') ?>" style="text-decoration:none;"><button>Our Work</button></a>
      <a href="<?= url('/reserve') ?>" style="text-decoration:none;"><button>Reserve</button></a>
      <a href="<?= url('/contact') ?>" style="text-decoration:none;"><button>Contact</button></a>
      <a href="<?= url('/dashboard') ?>" style="text-decoration:none;"><button class="active">Dashboard</button></a>
    </nav>
    <a href="<?= url('/logout') ?>" class="nav-cta" style="text-decoration:none; display:inline-flex;">Sign Out</a>
  </div>
</header>

<div class="shell section-tight">
  <div class="section-head" style="margin-bottom:20px;">
    <span class="eyebrow">Customer Dashboard</span>
    <h2>Welcome back, <?= e(explode(' ', $customer['full_name'])[0]) ?></h2>
  </div>

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
          <b><?= e($customer['full_name']) ?></b>
          <span><?= e($customer['email']) ?></span>
        </div>
      </div>
      <nav class="dash-nav">
        <a href="<?= url('/dashboard') ?>" style="text-decoration:none;">
          <button class="<?= dnav('overview', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/></svg>
            Overview
          </button>
        </a>
        <a href="<?= url('/dashboard/wallet') ?>" style="text-decoration:none;">
          <button class="<?= dnav('wallet', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><path d="M3 10H21M6 6H18C19.1 6 20 6.9 20 8V17C20 18.1 19.1 19 18 19H6C4.9 19 4 18.1 4 17V8C4 6.9 4.9 6 6 6Z" stroke="currentColor" stroke-width="1.6"/></svg>
            Wallet
          </button>
        </a>
        <a href="<?= url('/dashboard/attendance') ?>" style="text-decoration:none;">
          <button class="<?= dnav('attendance', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="15" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9H21M8 3V6M16 3V6" stroke="currentColor" stroke-width="1.6"/></svg>
            Attendance
          </button>
        </a>
        <a href="<?= url('/dashboard/family') ?>" style="text-decoration:none;">
          <button class="<?= dnav('family', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="9" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M3 20C3 16.5 5.7 14 9 14C12.3 14 15 16.5 15 20M15 20C15 17.3 16.8 15.5 19 15.5C20.5 15.5 21 16.5 21 20" stroke="currentColor" stroke-width="1.6"/></svg>
            Family &amp; Guest IDs
          </button>
        </a>
        <a href="<?= url('/dashboard/payments') ?>" style="text-decoration:none;">
          <button class="<?= dnav('payments', $activeNav) ?>">
            <svg viewBox="0 0 24 24" fill="none"><path d="M4 7L12 3L20 7M4 7V17L12 21L20 17V7M4 7L12 11M20 7L12 11M12 11V21" stroke="currentColor" stroke-width="1.6"/></svg>
            Payments
          </button>
        </a>
      </nav>
      <div class="dash-side-foot">
        <?php
          $sideSubs = $subscriptions ?? [];
          $activeSubs = array_filter($sideSubs, fn($s) => $s['status'] === 'active');
        ?>
        <?php if (count($activeSubs) > 1): ?>
          <?= count($activeSubs) ?> active plans
        <?php elseif (!empty($subscription)): ?>
          Plan renews <?= e(date('d M Y', strtotime($subscription['end_date']))) ?><br>
          <?= e($subscription['plan_name']) ?> — <?= e(ucfirst($subscription['duration'])) ?>
        <?php elseif (count($activeSubs) === 1):
          $only = array_values($activeSubs)[0];
        ?>
          Plan renews <?= e(date('d M Y', strtotime($only['end_date']))) ?><br>
          <?= e($only['plan_name']) ?> — <?= e(ucfirst($only['duration'])) ?>
        <?php else: ?>
          No active plan yet.
        <?php endif; ?>
      </div>
    </aside>

    <main class="dash-main">
