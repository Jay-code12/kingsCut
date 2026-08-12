<h2>Forgot your password?</h2>
<p class="sub">Enter your email and we'll send a 6-digit code to reset it.</p>

<form method="post" action="<?= url('/forgot-password/send') ?>">
  <?php csrf_field(); ?>
  <div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" placeholder="you@email.com" required autofocus>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Send Reset Code</button>
</form>

<div class="auth-foot">
  Remembered it? <a href="<?= url('/login') ?>">Sign in</a>
</div>
