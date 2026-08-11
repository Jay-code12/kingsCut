<h2>Welcome back</h2>
<p class="sub">Sign in to reach your membership dashboard.</p>

<form method="post" action="<?= url('/login') ?>">
  <?php csrf_field(); ?>
  <div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" placeholder="you@email.com" required autofocus>
  </div>
  <div class="field">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" placeholder="••••••••" required>
  </div>
  <div style="text-align:right; margin-top:-8px;">
    <a href="<?= url('/forgot-password') ?>" style="font-size:12.5px; color:var(--brass-bright); text-decoration:none;">Forgot password?</a>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Sign In</button>
</form>

<div class="auth-foot">
  New here? <a href="<?= url('/register') ?>">Create an account</a>
</div>
