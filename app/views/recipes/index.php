<?php
$pageTitle  = 'Recipes';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';

$search     = htmlspecialchars($_GET['search']     ?? '');
$difficulty = htmlspecialchars($_GET['difficulty'] ?? '');
$filter     = htmlspecialchars($_GET['filter']     ?? '');
$stars      = isset($_GET['stars']) && $_GET['stars'] !== '' ? (int)$_GET['stars'] : null;

function buildQuery(array $overrides = []): string {
    $base = [
        'search'     => $_GET['search']     ?? '',
        'difficulty' => $_GET['difficulty'] ?? '',
        'filter'     => $_GET['filter']     ?? '',
        'stars'      => $_GET['stars']      ?? '',
        'page'       => $_GET['page']       ?? 1,
    ];
    $params = array_merge($base, $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
}

$hasFilters = $search || $difficulty || $filter || $stars !== null;

$types = [
    'top'      => 'Top Rated',
    'original' => 'Originals',
    'remix'    => 'Remixes',
    'curated'  => 'Curated',
];

$diffLabels = [
    'Easy'         => 'Easy',
    'Intermediate' => 'Intermediate',
    'Hard'         => 'Hard',
];

$starLabels = [
    '0' => 'No ratings',
    '1' => '1 star',
    '2' => '2 stars',
    '3' => '3 stars',
    '4' => '4 stars',
    '5' => '5 stars',
];

$activeStarsKey = $stars !== null ? (string)$stars : '';
?>

<main>
  <div class="container">
    <div class="page-header">
      <div class="page-header-row">
        <div>
          <h1>Explore <span class="accent">Recipes</span></h1>
          <p>Discover curated dishes and community creations</p>
        </div>
        <a href="/add-recipe" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Recipe
        </a>
      </div>
    </div>

    <!-- Search + Dropdown Filters -->
    <div class="search-bar-wrap">
      <div class="search-filter-row">

        <!-- Search -->
        <form method="GET" action="/recipes" class="search-pill-form" id="search-form">
          <div class="search-pill">
            <span class="material-symbols-outlined">search</span>
            <input type="text" name="search" value="<?= $search ?>" placeholder="Search recipes...">
            <!-- Preserve other filters -->
            <?php if ($difficulty): ?><input type="hidden" name="difficulty" value="<?= $difficulty ?>"><?php endif; ?>
            <?php if ($filter): ?><input type="hidden" name="filter" value="<?= $filter ?>"><?php endif; ?>
            <?php if ($stars !== null): ?><input type="hidden" name="stars" value="<?= $stars ?>"><?php endif; ?>
            <?php if ($search): ?>
              <a href="<?= '/recipes' . buildQuery(['search' => '', 'page' => 1]) ?>" class="search-clear">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
          </div>
        </form>

        <!-- Dropdown Filters -->
        <div class="dropdown-filters">

          <!-- Mode (difficulty) -->
          <div class="dropdown-pill" id="dp-mode">
            <button type="button" class="dp-trigger <?= $difficulty ? 'dp-active' : '' ?>" onclick="toggleDp('dp-mode')">
              <?= $difficulty ?: 'Mode' ?>
              <span class="material-symbols-outlined dp-chevron">expand_more</span>
            </button>
            <div class="dp-menu">
              <?php if ($difficulty): ?>
              <a href="<?= '/recipes' . buildQuery(['difficulty' => '', 'page' => 1]) ?>" class="dp-item dp-clear">
                <span class="material-symbols-outlined" style="font-size:14px;">close</span> Clear
              </a>
              <div class="dp-divider"></div>
              <?php endif; ?>
              <?php foreach ($diffLabels as $val => $label):
                $active = $difficulty === $val;
              ?>
              <a href="<?= '/recipes' . buildQuery(['difficulty' => $val, 'page' => 1]) ?>"
                 class="dp-item <?= $active ? 'dp-selected' : '' ?>">
                <span class="diff-dot <?= strtolower($val) ?>"></span>
                <?= $label ?>
                <?php if ($active): ?><span class="material-symbols-outlined" style="font-size:14px; margin-left:auto;">check</span><?php endif; ?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Type -->
          <div class="dropdown-pill" id="dp-type">
            <button type="button" class="dp-trigger <?= $filter ? 'dp-active' : '' ?>" onclick="toggleDp('dp-type')">
              <?= $filter ? ($types[$filter] ?? 'Type') : 'Type' ?>
              <span class="material-symbols-outlined dp-chevron">expand_more</span>
            </button>
            <div class="dp-menu">
              <?php if ($filter): ?>
              <a href="<?= '/recipes' . buildQuery(['filter' => '', 'page' => 1]) ?>" class="dp-item dp-clear">
                <span class="material-symbols-outlined" style="font-size:14px;">close</span> Clear
              </a>
              <div class="dp-divider"></div>
              <?php endif; ?>
              <?php foreach ($types as $val => $label):
                $active = $filter === $val;
              ?>
              <a href="<?= '/recipes' . buildQuery(['filter' => $val, 'page' => 1]) ?>"
                 class="dp-item <?= $active ? 'dp-selected' : '' ?>">
                <?= $label ?>
                <?php if ($active): ?><span class="material-symbols-outlined" style="font-size:14px; margin-left:auto;">check</span><?php endif; ?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Rating -->
          <div class="dropdown-pill" id="dp-rating">
            <button type="button" class="dp-trigger <?= $stars !== null ? 'dp-active' : '' ?>" onclick="toggleDp('dp-rating')">
              <?php if ($stars === 0): ?>No ratings
              <?php elseif ($stars !== null): ?>
                <span style="color:<?= $stars !== null ? 'inherit' : '#f5a623' ?>;">★</span> <?= $stars ?> star<?= $stars !== 1 ? 's' : '' ?>
              <?php else: ?>Rating<?php endif; ?>
              <span class="material-symbols-outlined dp-chevron">expand_more</span>
            </button>
            <div class="dp-menu">
              <?php if ($stars !== null): ?>
              <a href="<?= '/recipes' . buildQuery(['stars' => '', 'page' => 1]) ?>" class="dp-item dp-clear">
                <span class="material-symbols-outlined" style="font-size:14px;">close</span> Clear
              </a>
              <div class="dp-divider"></div>
              <?php endif; ?>
              <a href="<?= '/recipes' . buildQuery(['stars' => '0', 'page' => 1]) ?>"
                 class="dp-item <?= $stars === 0 ? 'dp-selected' : '' ?>">
                No ratings
                <?php if ($stars === 0): ?><span class="material-symbols-outlined" style="font-size:14px; margin-left:auto;">check</span><?php endif; ?>
              </a>
              <?php for ($s = 1; $s <= 5; $s++):
                $active = $stars === $s;
              ?>
              <a href="<?= '/recipes' . buildQuery(['stars' => $s, 'page' => 1]) ?>"
                 class="dp-item <?= $active ? 'dp-selected' : '' ?>">
                <span class="dp-stars"><?= str_repeat('★', $s) ?><span style="color:var(--gray-300);"><?= str_repeat('★', 5 - $s) ?></span></span>
                <?= $s ?> star<?= $s !== 1 ? 's' : '' ?>
                <?php if ($active): ?><span class="material-symbols-outlined" style="font-size:14px; margin-left:auto;">check</span><?php endif; ?>
              </a>
              <?php endfor; ?>
            </div>
          </div>

          <?php if ($hasFilters): ?>
          <a href="/recipes" class="btn btn-ghost btn-sm" style="color:var(--pink);">
            <span class="material-symbols-outlined" style="font-size:16px;">close</span>
            Clear all
          </a>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- Results meta -->
    <?php if ($hasFilters): ?>
    <div class="results-meta">
      <?= $total ?> result<?= $total !== 1 ? 's' : '' ?>
      <?php if ($search): ?> for "<strong><?= $search ?></strong>"<?php endif; ?>
      <?php if ($difficulty): ?> &middot; <?= $difficulty ?><?php endif; ?>
      <?php if ($filter): ?> &middot; <?= $types[$filter] ?? $filter ?><?php endif; ?>
      <?php if ($stars === 0): ?> &middot; No ratings
      <?php elseif ($stars !== null): ?> &middot; <?= $stars ?> star<?= $stars !== 1 ? 's' : '' ?><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($recipes)): ?>
      <div class="empty-state">
        <p>No recipes found.</p>
        <?php if ($hasFilters): ?>
          <a href="/recipes" class="btn btn-outline btn-sm">Clear filters</a>
        <?php else: ?>
          <a href="/add-recipe" class="btn btn-pink btn-sm">Add the first one</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
    <div class="recipes-grid">
      <?php foreach ($recipes as $r):
        $diff        = htmlspecialchars($r['difficulty']);
        $cls         = strtolower($diff);
        $id          = (int)$r['recipe_id'];
        $isRemix     = !empty($r['remixed_from']);
        $avgRating   = round((float)($r['avg_rating'] ?? 0), 1);
        $ratingCount = (int)($r['rating_count'] ?? 0);
      ?>
      <article class="recipe-card">
        <div class="card-img">
          <img src="<?= htmlspecialchars($r['image_url']) ?>"
               alt="<?= htmlspecialchars($r['title']) ?>"
               loading="lazy"
               onerror="this.src='https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600'">
          <span class="card-badge <?= $cls ?>"><?= $diff ?></span>
        </div>
        <div class="card-body">
          <div class="card-head">
            <h3><?= htmlspecialchars($r['title']) ?></h3>
            <?php if ($isRemix): ?>
              <div class="remix-tag"><?= htmlspecialchars($r['original_title']) ?><span class="material-symbols-outlined">shuffle</span></div>
            <?php endif; ?>
          </div>

          <?php if ($ratingCount > 0): ?>
          <div class="recipe-card-rating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <span class="star <?= $i <= round($avgRating) ? 'filled' : '' ?>">★</span>
            <?php endfor; ?>
            <span><?= $avgRating ?> (<?= $ratingCount ?>)</span>
          </div>
          <?php endif; ?>

          <div class="card-footer-row" style="margin-top:<?= ($isRemix || $ratingCount > 0) ? '12px' : '0' ?>">
            <span style="font-size:.8rem; color:var(--gray-500); font-weight:500;">
              <?php if ($r['is_premade']): ?>Curated
              <?php elseif (!empty($r['first_name'])): ?><?= htmlspecialchars($r['first_name']) ?>
              <?php else: ?>Community<?php endif; ?>
            </span>
            <a href="/recipe?id=<?= $id ?>" class="btn btn-primary btn-sm">
              See Recipe
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($currentPage > 1): ?>
        <a href="<?= '/recipes' . buildQuery(['page' => $currentPage - 1]) ?>" class="page-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
      <?php else: ?>
        <span class="page-btn disabled"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg></span>
      <?php endif; ?>

      <?php
      $start = max(1, $currentPage - 2);
      $end   = min($totalPages, $currentPage + 2);
      if ($start > 1): ?>
        <a href="<?= '/recipes' . buildQuery(['page' => 1]) ?>" class="page-btn">1</a>
        <?php if ($start > 2): ?><span class="page-dots">...</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i === $currentPage): ?>
          <span class="page-btn active"><?= $i ?></span>
        <?php else: ?>
          <a href="<?= '/recipes' . buildQuery(['page' => $i]) ?>" class="page-btn"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?><span class="page-dots">...</span><?php endif; ?>
        <a href="<?= '/recipes' . buildQuery(['page' => $totalPages]) ?>" class="page-btn"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($currentPage < $totalPages): ?>
        <a href="<?= '/recipes' . buildQuery(['page' => $currentPage + 1]) ?>" class="page-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      <?php else: ?>
        <span class="page-btn disabled"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<script>
function toggleDp(id) {
  const all = document.querySelectorAll('.dropdown-pill');
  all.forEach(dp => {
    if (dp.id !== id) dp.classList.remove('open');
  });
  document.getElementById(id).classList.toggle('open');
}

document.addEventListener('click', (e) => {
  if (!e.target.closest('.dropdown-pill')) {
    document.querySelectorAll('.dropdown-pill').forEach(dp => dp.classList.remove('open'));
  }
});
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>