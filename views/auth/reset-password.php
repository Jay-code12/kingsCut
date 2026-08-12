<?php
/** @var string $maskedEmail */
?>
<h2>Enter your code</h2>
<p class="sub">We sent a 6-digit code to <strong style="color:var(--brass-bright);"><?= e($maskedEmail) ?></strong>. It expires in 10 minutes.</p>

<form method="post" action="<?= url('/reset-password') ?>">
  <?php csrf_field(); ?>
  <div class="field">
    <label for="otp">6-digit code</label>
    <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
           placeholder="123456" required autofocus
           style="letter-spacing:8px; font-family:'JetBrains Mono',monospace; font-size:18px; text-align:center;">
  </div>
  <div class="field">
    <label for="password">New password</label>
    <input id="password" name="password" type="password" placeholder="At least 8 characters" required>
  </div>
  <div class="field">
    <label for="password_confirm">Confirm new password</label>
    <input id="password_confirm" name="password_confirm" type="password" placeholder="Repeat your new password" required>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
</form>

<form method="post" action="<?= url('/forgot-password/resend') ?>" style="margin-top:14px;">
  <?php csrf_field(); ?>
  <button type="submit" class="btn btn-outline btn-block">Resend Code</button>
</form>

<div class="auth-foot">
  <a href="<?= url('/forgot-password') ?>">Use a different email</a>
</div>
