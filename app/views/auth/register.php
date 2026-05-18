<?php
$pageTitle = 'Create Account';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
?>

<main class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <h1>Create account</h1>
      <p>Join the Wandering Magnolias community</p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/register">
      <div class="form-row">
        <div class="form-group">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name" placeholder="Andrea" required autofocus>
        </div>
        <div class="form-group">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name" placeholder="To Chip" required>
        </div>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="andreatochip@gmail.com" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
      </div>
      <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-lg auth-submit">Create Account →</button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="/login">Sign in</a>
    </div>
  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>
