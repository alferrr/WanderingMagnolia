<?php
$pageTitle = htmlspecialchars($recipe['title']);
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
$diff      = htmlspecialchars($recipe['difficulty']);
$diffCls   = strtolower($diff);
$id        = (int)$recipe['recipe_id'];
$isOwner   = !empty($_SESSION['user_id'])
          && (int)$recipe['user_id'] === (int)$_SESSION['user_id']
          && !$recipe['is_premade'];
$isRemix   = !empty($recipe['remixed_from']);
$isLoggedIn = !empty($_SESSION['user_id']);
$canRate    = $isLoggedIn && !$isOwner && !$recipe['is_premade'];
$avgRating  = round((float)$recipe['avg_rating'], 1);
$ratingCount = (int)$recipe['rating_count'];

function renderStars(float $avg, bool $interactive = false, int $userStars = 0): string {
    $html = '<div class="stars' . ($interactive ? ' stars-interactive' : '') . '">';
    for ($i = 1; $i <= 5; $i++) {
        $filled = $interactive ? ($i <= $userStars) : ($i <= round($avg));
        $cls    = $filled ? 'star filled' : 'star';
        if ($interactive) {
            $html .= '<button type="button" class="' . $cls . '" data-star="' . $i . '" aria-label="' . $i . ' stars">★</button>';
        } else {
            $html .= '<span class="' . $cls . '">★</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>

<main>
  <div class="container">

    <div style="padding-top:24px; font-size:.85rem; color:var(--gray-500);">
      <a href="/recipes" style="color:var(--pink);">← Back to Recipes</a>
    </div>

    <!-- Hero -->
    <div class="recipe-hero">
      <img src="<?= htmlspecialchars($recipe['image_url']) ?>"
           alt="<?= htmlspecialchars($recipe['title']) ?>"
           onerror="this.src='https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200'">
      <div class="recipe-hero-overlay">
        <div>
          <?php if ($isRemix): ?>
          <div class="remix-credit-badge">
            Remixed from
            <a href="/recipe?id=<?= (int)$recipe['original_id'] ?>">
              <?= htmlspecialchars($recipe['original_title']) ?>
            </a>
          </div>
          <?php else: ?>
          <div style="font-size:.85rem; color:rgba(255,255,255,.7); margin-bottom:8px; font-family:var(--font-head);">Let's Cook</div>
          <?php endif; ?>
          <h1><?= htmlspecialchars($recipe['title']) ?></h1>
          <?php if ($ratingCount > 0): ?>
          <div class="recipe-hero-rating">
            <?= renderStars($avgRating) ?>
            <span><?= $avgRating ?> (<?= $ratingCount ?> <?= $ratingCount === 1 ? 'rating' : 'ratings' ?>)</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Meta Bar -->
    <div class="recipe-meta-bar">
      <div class="meta-item">
        <span class="meta-label">Difficulty</span>
        <span class="meta-value" style="color:<?= $diffCls === 'easy' ? '#3D8B3D' : ($diffCls === 'hard' ? 'var(--pink)' : '#C47900') ?>"><?= $diff ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Ingredients</span>
        <span class="meta-value"><?= count($ingredients) ?> Items</span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Steps</span>
        <span class="meta-value"><?= count($directions) ?> Steps</span>
      </div>
      <?php if (!empty($recipe['first_name'])): ?>
      <div class="meta-item">
        <span class="meta-label">By</span>
        <span class="meta-value"><?= htmlspecialchars($recipe['first_name'] . ' ' . $recipe['last_name']) ?></span>
      </div>
      <?php endif; ?>
      <div class="meta-item" style="margin-left:auto; display:flex; gap:10px;">
        <?php if ($isOwner): ?>
          <a href="/edit-recipe?id=<?= $id ?>" class="btn btn-outline btn-sm">Edit Recipe</a>
        <?php elseif ($isLoggedIn && !$recipe['is_premade']): ?>
          <a href="/remix-recipe?id=<?= $id ?>" class="btn btn-remix btn-sm">
            Remix This Recipe
          </a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($_SESSION['remix_blocked'])): ?>
    <div class="alert alert-error" style="margin-top:16px;">
      <span class="material-symbols-outlined" style="font-size:16px;">info</span>
      <?= htmlspecialchars($_SESSION['remix_blocked']) ?>
      <?php unset($_SESSION['remix_blocked']); ?>
    </div>
    <?php endif; ?>

    <!-- Body -->
    <div class="recipe-body">
      <aside class="ingredients-card">
        <h2>Ingredients</h2>
        <ul>
          <?php foreach ($ingredients as $ing): ?>
          <li>
            <span class="dot"></span>
            <span class="qty">
              <?= $ing['base_quantity'] != 1 || $ing['unit'] ? htmlspecialchars($ing['base_quantity'] . ' ' . $ing['unit']) : '' ?>
            </span>
            <span><?= htmlspecialchars($ing['name']) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="grocery-btn-wrap">
          <a href="/grocery?id=<?= $id ?>" class="btn btn-pink">
            Generate Grocery List
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </aside>

      <section class="directions-section">
        <h2>Cooking <span class="accent">Instructions</span></h2>
        <?php foreach ($directions as $dir): ?>
        <div class="direction-step">
          <div class="step-num"><?= str_pad($dir['step_number'], 2, '0', STR_PAD_LEFT) ?></div>
          <div class="step-text"><?= htmlspecialchars($dir['instruction']) ?></div>
        </div>
        <?php endforeach; ?>
      </section>
    </div>

    <!-- Ratings Section -->
    <?php if (!$recipe['is_premade']): ?>
    <div class="ratings-section">
      <h2>Ratings <span class="accent">&amp; Reviews</span></h2>

      <?php if (!empty($ratingError)): ?>
      <div class="alert alert-error" style="margin-bottom:16px;">
        <span class="material-symbols-outlined" style="font-size:16px;">info</span>
        <?= htmlspecialchars($ratingError) ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($ratingSuccess)): ?>
      <div class="alert alert-success" style="margin-bottom:16px;">
        <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
        <?= htmlspecialchars($ratingSuccess) ?>
      </div>
      <?php endif; ?>

      <!-- Rating Form -->
      <?php if ($canRate): ?>
      <div class="rating-form-card">
        <h3><?= $hasRated ? 'Update Your Rating' : 'Rate This Recipe' ?></h3>
        <form method="POST" action="/recipe/rate" id="rating-form">
          <input type="hidden" name="recipe_id" value="<?= $id ?>">
          <input type="hidden" name="stars" id="stars-input" value="<?= $userRating['stars'] ?? 0 ?>">

          <div class="rating-stars-row">
            <?= renderStars($avgRating, true, $userRating['stars'] ?? 0) ?>
            <span class="rating-label" id="rating-label">
              <?php
              $labels = ['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'];
              echo $labels[$userRating['stars'] ?? 0] ?? 'Select a rating';
              ?>
            </span>
          </div>

          <div class="form-group review-block" style="margin-top:16px;">
            <label for="review">Review <span style="color:var(--gray-400); font-weight:400;">(optional)</span></label>
            <textarea id="review" name="review" rows="3"
              placeholder="Share your thoughts on this recipe..."><?= htmlspecialchars($userRating['review'] ?? '') ?></textarea>
          </div>

          <div style="display:flex; gap:10px; margin-top:16px;">
            <button type="submit" class="btn btn-primary btn-sm">
              <?= $hasRated ? 'Update Rating' : 'Submit Rating' ?>
            </button>
            <?php if ($hasRated): ?>
            <form method="POST" action="/recipe/delete-rating" style="margin:0;">
              <input type="hidden" name="recipe_id" value="<?= $id ?>">
              <button type="submit" class="btn btn-ghost btn-sm">Remove Rating</button>
            </form>
            <?php endif; ?>
          </div>
        </form>
      </div>
      <?php elseif (!$isLoggedIn): ?>
      <div class="rating-login-prompt">
        <span class="material-symbols-outlined">star</span>
        <p><a href="/login">Sign in</a> to rate this recipe.</p>
      </div>
      <?php endif; ?>

      <!-- Existing Ratings -->
      <?php if (!empty($ratings)): ?>
      <div class="ratings-list">
        <?php foreach ($ratings as $r): ?>
        <div class="rating-item">
          <div class="rating-item-header">
            <div class="rating-avatar"><?= strtoupper($r['first_name'][0]) ?></div>
            <div class="rating-meta">
              <span class="rating-name"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></span>
              <span class="rating-date"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
            </div>
            <div class="rating-stars-display">
              <?= renderStars((float)$r['stars']) ?>
            </div>
          </div>
          <?php if (!empty($r['review'])): ?>
          <p class="rating-review"><?= htmlspecialchars($r['review']) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="rating-empty">
        <span class="material-symbols-outlined">star_border</span>
        <p>No ratings yet. Be the first to rate this recipe.</p>
      </div>
      <?php endif; ?>

    </div>
    <?php endif; ?>

  </div>
</main>

<script>
(function() {
  const buttons  = document.querySelectorAll('.stars-interactive .star');
  const input    = document.getElementById('stars-input');
  const label    = document.getElementById('rating-label');
  const labels   = ['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'];

  if (!buttons.length) return;

  function setStars(val) {
    buttons.forEach((btn, i) => {
      btn.classList.toggle('filled', i < val);
    });
    if (input)  input.value    = val;
    if (label)  label.textContent = labels[val] || 'Select a rating';
  }

  // Set initial state
  setStars(parseInt(input?.value || 0));

  buttons.forEach((btn, i) => {
    btn.addEventListener('click',      () => setStars(i + 1));
    btn.addEventListener('mouseenter', () => setStars(i + 1));
  });

  document.querySelector('.stars-interactive')
    ?.addEventListener('mouseleave', () => setStars(parseInt(input?.value || 0)));
})();
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>