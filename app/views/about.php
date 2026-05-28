<?php
// app/views/about.php
$pageTitle = 'About Us';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
?>

<main>
  <div class="container">

    <!-- Hero -->
    <div class="about-hero">
      <div class="about-eyebrow">
        <span class="material-symbols-outlined">info</span>
        About the Project
      </div>
      <h1>Made with curiosity,<br><span class="accent">served with passion</span></h1>
      <p>Wandering Magnolias is a community-driven recipe sharing platform built as a learning project for PHP and as a Final Web Development Project.</p>
    </div>

    <div class="about-divider"></div>

    <!-- Creators -->
    <div class="about-section">
      <h2 class="about-section-title">The People <span class="accent">Behind It</span></h2>
      <div class="about-creators">

        <div class="creator-card">
          <div class="creator-avatar">A</div>
          <div class="creator-info">
            <h3>Alfer Brent Mercado</h3>
            <span class="creator-role">Lead Developer</span>
            <p>Handled the full-stack architecture, backend logic, database design, and deployment. Built the application from scratch using PHP MVC, MySQL, PHPMailer, and Apache.</p>
            <div class="creator-tags">
              <span>PHP</span>
              <span>MySQL</span>
              <span>MVC</span>
              <span>Apache</span>
            </div>
          </div>
        </div>

        <div class="creator-card">
          <div class="creator-avatar creator-avatar-pink">A</div>
          <div class="creator-info">
            <h3>Andrea Keisha To Chip</h3>
            <span class="creator-role">Project Lead & Designer</span>
            <p>Conceptualized the project, defined the application's scope and features, and designed the database schema — bringing the idea of a community recipe sharing platform to life as her Final Web Development Project.</p>
            <div class="creator-tags">
              <span>PHP</span>
              <span>MySQL</span>
              <span>Database Design</span>
              <span>MVC</span>
              <span>Project Management</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="about-divider"></div>

    <!-- Stack -->
<div class="about-section">
  <h2 class="about-section-title">Built <span class="accent">With</span></h2>
  <div class="about-stack">
    <div class="stack-item">
      <span class="material-symbols-outlined">code</span>
      <span class="stack-name">PHP 8.2</span>
      <span class="stack-desc">Backend & MVC</span>
    </div>
    <div class="stack-item">
      <span class="material-symbols-outlined">storage</span>
      <span class="stack-name">MySQL</span>
      <span class="stack-desc">Database</span>
    </div>
    <div class="stack-item">
      <span class="material-symbols-outlined">mail</span>
      <span class="stack-name">PHPMailer</span>
      <span class="stack-desc">Email Delivery</span>
    </div>
    <div class="stack-item">
      <span class="material-symbols-outlined">dns</span>
      <span class="stack-name">Apache</span>
      <span class="stack-desc">Web Server</span>
    </div>
    <div class="stack-item">
      <span class="material-symbols-outlined">cloud</span>
      <span class="stack-name">DCISM</span>
      <span class="stack-desc">Hosting</span>
    </div>
    <div class="stack-item">
      <span class="material-symbols-outlined">palette</span>
      <span class="stack-name">Vanilla CSS</span>
      <span class="stack-desc">Styling</span>
    </div>
  </div>
</div>

    <div class="about-divider"></div>

    <!-- Features -->
    <div class="about-section about-features">
      <h2 class="about-section-title">What We <span class="accent">Built</span></h2>
      <div class="about-feature-grid">
        <div class="about-feature-item">
          <span class="material-symbols-outlined">restaurant_menu</span>
          <div>
            <h4>Recipe Sharing</h4>
            <p>Browse, create, and share recipes with a community of food lovers.</p>
          </div>
        </div>
        <div class="about-feature-item">
          <span class="material-symbols-outlined">shuffle</span>
          <div>
            <h4>Recipe Remixing</h4>
            <p>Put your own spin on existing recipes with full credit to the original. Rate first before you can remix.</p>
          </div>
        </div>
        <div class="about-feature-item">
          <span class="material-symbols-outlined">star</span>
          <div>
            <h4>Ratings & Reviews</h4>
            <p>Rate recipes 1–5 stars and leave a written review. Averages shown on every card.</p>
          </div>
        </div>
        <div class="about-feature-item">
          <span class="material-symbols-outlined">shopping_cart</span>
          <div>
            <h4>Grocery Lists</h4>
            <p>Auto-generate scaled grocery lists from any recipe with fraction formatting and a serving scaler.</p>
          </div>
        </div>
        <div class="about-feature-item">
          <span class="material-symbols-outlined">lock_reset</span>
          <div>
            <h4>OTP Password Reset</h4>
            <p>Secure forgot password flow with a 6-digit OTP sent to your email with a 15-minute expiry.</p>
          </div>
        </div>
        <div class="about-feature-item">
          <span class="material-symbols-outlined">delete</span>
          <div>
            <h4>Trash & Recovery</h4>
            <p>Soft-delete with a 30-day recovery window before permanent removal.</p>
          </div>
        </div>
        <div class="about-feature-item">
          <span class="material-symbols-outlined">admin_panel_settings</span>
          <div>
            <h4>Admin Panel</h4>
            <p>Full dashboard for managing users and recipes, with trash bins, stats, and charts.</p>
          </div>
        </div>
        <div class="about-feature-item">
          <span class="material-symbols-outlined">filter_list</span>
          <div>
            <h4>Smart Filtering</h4>
            <p>Filter recipes by difficulty, type (Original, Remix, Curated, Top Rated), and star rating.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA -->
    <div class="about-cta">
      <h2>Ready to start cooking?</h2>
      <p>Browse our curated collection or share a recipe of your own.</p>
      <div class="about-cta-actions">
        <a href="/recipes" class="btn btn-primary btn-lg">
          <span class="material-symbols-outlined">restaurant_menu</span>
          Browse Recipes
        </a>
        <a href="/register" class="btn btn-outline btn-lg">
          <span class="material-symbols-outlined">person_add</span>
          Create Account
        </a>
      </div>
    </div>

  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>