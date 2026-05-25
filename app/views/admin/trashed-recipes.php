<?php
// app/views/admin/trashed-recipes.php
$pageTitle = 'Admin — Trashed Recipes';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
?>

<main>
  <div class="container">

    <div class="page-header">
      <div>
        <a href="/admin/recipes" style="font-size:.85rem; color:var(--pink);">← Back to Recipes</a>
        <h1 style="margin-top:8px;">Trashed <span class="accent">Recipes</span></h1>
        <p><?= count($recipes) ?> recipe<?= count($recipes) !== 1 ? 's' : '' ?> in trash</p>
      </div>
    </div>

    <?php if (empty($recipes)): ?>
      <div class="account-empty" style="margin-bottom:80px;">
        <span class="material-symbols-outlined" style="font-size:3rem; color:var(--gray-300); display:block; margin-bottom:16px;">delete_sweep</span>
        <h3>Trash is empty</h3>
        <p>No recipes have been deleted.</p>
        <a href="/admin/recipes" class="btn btn-outline btn-sm" style="margin-top:8px;">Back to Recipes</a>
      </div>
    <?php else: ?>

    <div class="admin-table-wrap" style="margin-bottom:80px;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Recipe</th>
            <th>Author</th>
            <th>Difficulty</th>
            <th>Deleted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recipes as $r): ?>
          <tr style="opacity:.75;">
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <img src="<?= htmlspecialchars($r['image_url']) ?>"
                     style="width:40px; height:40px; object-fit:cover; border-radius:8px; flex-shrink:0; filter:grayscale(40%);"
                     onerror="this.src='https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=80'">
                <span style="font-weight:500; text-decoration:line-through; color:var(--gray-500);">
                  <?= htmlspecialchars($r['title']) ?>
                </span>
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
            <td style="color:var(--gray-500); font-size:.82rem; white-space:nowrap;">
              <?php
                $deletedAt = new DateTime($r['deleted_at']);
                $expiresAt = (clone $deletedAt)->modify('+30 days');
                $now       = new DateTime();
                $daysLeft  = max(0, (int)$now->diff($expiresAt)->days);
              ?>
              <?= date('M j, Y', strtotime($r['deleted_at'])) ?>
              <div class="trash-expiry <?= $daysLeft <= 3 ? 'urgent' : '' ?>" style="margin-top:4px; display:inline-flex;">
                <span class="material-symbols-outlined" style="font-size:11px;">schedule</span>
                <?= $daysLeft === 0 ? 'Expires today' : ($daysLeft === 1 ? '1 day left' : $daysLeft . ' days left') ?>
              </div>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <!-- Restore -->
                <form method="POST" action="/admin/recipe/restore" style="margin:0;">
                  <input type="hidden" name="recipe_id" value="<?= $r['recipe_id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm" title="Restore">
                    <span class="material-symbols-outlined">restore_from_trash</span>
                    Restore
                  </button>
                </form>
                <!-- Permanent delete -->
                <form method="POST" action="/admin/recipe/delete-permanent" style="margin:0;">
                  <input type="hidden" name="recipe_id" value="<?= $r['recipe_id'] ?>">
                  <button type="submit" class="btn btn-delete btn-sm" title="Delete permanently">
                    <span class="material-symbols-outlined">delete_forever</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php endif; ?>
  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>