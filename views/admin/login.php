<h2>Admin Console</h2>
<p class="sub">Sign in to manage plans, services, and view sales.</p>

<form method="post" action="<?= url('/admin/login') ?>">
  <?php csrf_field(); ?>
  <div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" placeholder="you@kingscutsaloon.com" required autofocus>
  </div>
  <div class="field">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" placeholder="••••••••" required>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Sign In</button>
</form>

<div class="auth-foot">
  <a href="<?= url('/') ?>">&larr; Back to the main site</a>
</div>
