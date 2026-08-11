<h2>Create your account</h2>
<p class="sub">Then choose a membership plan whenever you're ready.</p>

<form method="post" action="<?= url('/register') ?>">
  <?php csrf_field(); ?>
  <div class="field">
    <label for="full_name">Full name</label>
    <input id="full_name" name="full_name" type="text" placeholder="Alex Morgan" required autofocus>
  </div>
  <div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" placeholder="you@email.com" required>
  </div>
  <div class="field">
    <label for="phone">Phone (optional)</label>
    <input id="phone" name="phone" type="tel" placeholder="+234 800 000 0000">
  </div>
  <div class="field">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" placeholder="At least 8 characters" required>
  </div>
  <div class="field">
    <label for="password_confirm">Confirm password</label>
    <input id="password_confirm" name="password_confirm" type="password" placeholder="Repeat your password" required>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Create Account</button>
</form>

<div class="auth-foot">
  Already a member? <a href="<?= url('/login') ?>">Sign in</a>
</div>
