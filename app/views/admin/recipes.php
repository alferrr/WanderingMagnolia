<?php
$pageTitle = 'Admin — Recipes';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
$searchVal = htmlspecialchars($_GET['search'] ?? '');

function adminRecipesUrl(array $overrides = []): string {
    global $search, $currentPage;
    $params = [];
    $s = $overrides['search'] ?? $search;
    $p = $overrides['page']   ?? $currentPage;
    if ($s !== '') $params['search'] = $s;
    if ($p > 1)   $params['page']   = $p;
    return '/admin/recipes' . ($params ? '?' . http_build_query($params) : '');
}
?>

<main>
  <div class="container">

    <div class="page-header">
      <div>
        <a href="/admin" style="font-size:.85rem; color:var(--pink);">← Dashboard</a>
        <h1 style="margin-top:8px;">Manage <span class="accent">Recipes</span></h1>
        <p>
          Showing <?= count($recipes) ?> of <?= $total ?> recipe<?= $total !== 1 ? 's' : '' ?>
          <?= $search !== '' ? ' for "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
        </p>
      </div>
      <?php if ($trashedCount > 0): ?>
      <a href="/admin/recipes/trash" class="btn btn-ghost btn-sm" style="position:relative; margin-left:auto; align-self:flex-start; margin-top:8px;">
        <span class="material-symbols-outlined">delete</span>
        Trash
        <span class="trash-badge"><?= $trashedCount ?></span>
      </a>
      <?php else: ?>
      <a href="/admin/recipes/trash" class="btn btn-ghost btn-sm" style="margin-left:auto; align-self:flex-start; margin-top:8px;">
        <span class="material-symbols-outlined">delete</span>
        Trash
      </a>
      <?php endif; ?>
    </div>

    <!-- Search -->
    <form method="GET" action="/admin/recipes" class="account-search-form" style="margin-bottom:24px;">
      <div class="search-pill">
        <span class="material-symbols-outlined search-icon" style="font-size:18px;">search</span>
        <input type="text" name="search" value="<?= $searchVal ?>"
               placeholder="Search by title or author...">
        <?php if ($searchVal): ?>
          <a href="/admin/recipes" class="search-clear">
            <span class="material-symbols-outlined" style="font-size:16px;">close</span>
          </a>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Search</button>
    </form>

    <?php if (empty($recipes)): ?>
      <div class="account-empty" style="margin-bottom:80px;">
        <h3>No recipes found</h3>
        <?php if ($search !== ''): ?>
          <p>No recipes match your search.</p>
          <a href="/admin/recipes" class="btn btn-outline btn-sm">Clear search</a>
        <?php endif; ?>
      </div>
    <?php else: ?>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Recipe</th>
            <th>Author</th>
            <th>Difficulty</th>
            <th>Type</th>
            <th>Rating</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recipes as $r): ?>
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <img src="<?= htmlspecialchars($r['image_url']) ?>"
                     style="width:40px; height:40px; object-fit:cover; border-radius:8px; flex-shrink:0;"
                     onerror="this.src='https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=80'">
                <span style="font-weight:500;"><?= htmlspecialchars($r['title']) ?></span>
              </div>
            </td>
            <td>
              <?php if ($r['first_name']): ?>
                <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>
              <?php else: ?>
                <span style="color:var(--gray-400);">Curated</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="diff-tag <?= strtolower($r['difficulty']) ?>">
                <?= htmlspecialchars($r['difficulty']) ?>
              </span>
            </td>
            <td>
              <?php if ($r['is_premade']): ?>
                <span style="font-size:.78rem; color:var(--gray-500);">Curated</span>
              <?php elseif ($r['remixed_from']): ?>
                <span style="font-size:.78rem; color:var(--pink);">Remix</span>
              <?php else: ?>
                <span style="font-size:.78rem; color:var(--gray-700);">Original</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($r['rating_count'] > 0): ?>
                <span style="color:#f5a623;">★</span>
                <?= round($r['avg_rating'], 1) ?>
                <span style="color:var(--gray-400); font-size:.78rem;">(<?= $r['rating_count'] ?>)</span>
              <?php else: ?>
                <span style="color:var(--gray-400);">—</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--gray-500); font-size:.82rem; white-space:nowrap;">
              <?= date('M j, Y', strtotime($r['created_at'])) ?>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <a href="/recipe?id=<?= $r['recipe_id'] ?>" class="btn btn-ghost btn-sm" title="View">
                  <span class="material-symbols-outlined">visibility</span>
                </a>
                <a href="/admin/recipe/edit?id=<?= $r['recipe_id'] ?>" class="btn btn-outline btn-sm" title="Edit">
                  <span class="material-symbols-outlined">edit</span>
                </a>
                <form method="POST" action="/admin/recipe/delete" style="margin:0;">
                  <input type="hidden" name="recipe_id" value="<?= $r['recipe_id'] ?>">
                  <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                  <input type="hidden" name="page" value="<?= $currentPage ?>">
                  <button type="submit" class="btn btn-delete btn-sm" title="Move to trash">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="padding-block: 24px 48px;">
      <?php if ($currentPage > 1): ?>
        <a href="<?= adminRecipesUrl(['page' => $currentPage - 1]) ?>" class="page-btn">
          <span class="material-symbols-outlined">chevron_left</span>
        </a>
      <?php else: ?>
        <span class="page-btn disabled"><span class="material-symbols-outlined">chevron_left</span></span>
      <?php endif; ?>

      <?php
      $start = max(1, $currentPage - 2);
      $end   = min($totalPages, $currentPage + 2);
      if ($start > 1): ?>
        <a href="<?= adminRecipesUrl(['page' => 1]) ?>" class="page-btn">1</a>
        <?php if ($start > 2): ?><span class="page-dots">...</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i === $currentPage): ?>
          <span class="page-btn active"><?= $i ?></span>
        <?php else: ?>
          <a href="<?= adminRecipesUrl(['page' => $i]) ?>" class="page-btn"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?><span class="page-dots">...</span><?php endif; ?>
        <a href="<?= adminRecipesUrl(['page' => $totalPages]) ?>" class="page-btn"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($currentPage < $totalPages): ?>
        <a href="<?= adminRecipesUrl(['page' => $currentPage + 1]) ?>" class="page-btn">
          <span class="material-symbols-outlined">chevron_right</span>
        </a>
      <?php else: ?>
        <span class="page-btn disabled"><span class="material-symbols-outlined">chevron_right</span></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>