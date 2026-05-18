<?php
$pageTitle = 'Sign In';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
$success = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_success']);
?>

<main class="auth-page">
  <div class="photo"></div>
  <div class="auth-card">
    <div class="auth-logo">
      <h1>Welcome back</h1>
      <p>Sign in to your Wandering Magnolias account</p>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert alert-success">
      <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
      <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      <span class="material-symbols-outlined" style="font-size:16px;">info</span>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/login">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="andreatochip@gmail.com" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-field">
          <input type="password" id="password" name="password" placeholder="••••••••" required>
          <button type="button" class="password-toggle" id="toggle-pass" title="Show password">
            <span class="material-symbols-outlined" id="toggle-icon">visibility</span>
          </button>
        </div>
        <div style="text-align:right; margin-top:8px;">
          <a href="/forgot-password" style="font-size:.82rem; color:var(--pink); font-weight:600;">Forgot password?</a>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-lg auth-submit">Sign In →</button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="/register">Create one</a>
    </div>
  </div>
</main>

<script>
document.getElementById('toggle-pass').addEventListener('click', function() {
  const input = document.getElementById('password');
  const icon  = document.getElementById('toggle-icon');
  if (input.type === 'password') {
    input.type = 'text'; icon.textContent = 'visibility_off';
  } else {
    input.type = 'password'; icon.textContent = 'visibility';
  }
});
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>