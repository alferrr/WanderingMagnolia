<?php
// app/views/admin/index.php
$pageTitle = 'Admin Dashboard';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';

// Count recipe types from already-fetched data
$allRecipes   = (new RecipeModel())->getAllForAdmin();
$easyCount    = count(array_filter($allRecipes, fn($r) => $r['difficulty'] === 'Easy'    && !$r['is_deleted']));
$interCount   = count(array_filter($allRecipes, fn($r) => $r['difficulty'] === 'Intermediate' && !$r['is_deleted']));
$hardCount    = count(array_filter($allRecipes, fn($r) => $r['difficulty'] === 'Hard'    && !$r['is_deleted']));
$curatedCount = count(array_filter($allRecipes, fn($r) => $r['is_premade']  && !$r['is_deleted']));
$originalCount= count(array_filter($allRecipes, fn($r) => !$r['is_premade'] && !$r['remixed_from'] && !$r['is_deleted']));
$remixCount   = count(array_filter($allRecipes, fn($r) => $r['remixed_from'] && !$r['is_deleted']));

$totalRatings = 0;
try {
    $db   = Database::connect();
    $stmt = $db->query('SELECT COUNT(*) FROM ratings');
    $totalRatings = (int) $stmt->fetchColumn();
} catch (\Exception $e) {}

$maxDiff = max($easyCount, $interCount, $hardCount, 1);
$maxType = max($curatedCount, $originalCount, $remixCount, 1);
?>

<main class="admin">
  <div class="container">

    <div class="about-eyebrow admin-header" style="margin-bottom:12px;">
      <span class="material-symbols-outlined">admin_panel_settings</span>
      Admin Panel
    </div>
    <h1 style="margin-bottom:4px;">Dashboard</h1>
    <p style="color:var(--gray-500); margin-bottom:32px;">Overview of all activity on Wandering Magnolias.</p>

    <!-- Stat Cards -->
    <div class="admin-stats" style="grid-template-columns: repeat(4, 1fr); margin-bottom:24px;">
      <div class="admin-stat-card">
        <span class="material-symbols-outlined" style="color:var(--pink);">group</span>
        <div>
          <div class="stat-value"><?= $totalUsers ?></div>
          <div class="stat-label">Total users</div>
        </div>
      </div>
      <div class="admin-stat-card">
        <span class="material-symbols-outlined" style="color:var(--pink);">restaurant_menu</span>
        <div>
          <div class="stat-value"><?= $totalRecipes ?></div>
          <div class="stat-label">Total recipes</div>
        </div>
      </div>
      <div class="admin-stat-card">
        <span class="material-symbols-outlined" style="color:<?= $trashedRecipes > 0 ? '#C47900' : 'var(--gray-400)' ?>;">delete</span>
        <div>
          <div class="stat-value"><?= $trashedRecipes ?></div>
          <div class="stat-label">In trash</div>
        </div>
      </div>
      <div class="admin-stat-card">
        <span class="material-symbols-outlined" style="color:#f5a623;">star</span>
        <div>
          <div class="stat-value"><?= $totalRatings ?></div>
          <div class="stat-label">Total ratings</div>
        </div>
      </div>
    </div>

    <!-- Alerts -->
    <?php if ($trashedRecipes > 0 || $archivedUsers > 0): ?>
    <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:24px;">
      <?php if ($trashedRecipes > 0): ?>
      <div class="alert alert-error" style="display:flex; align-items:center; gap:10px;">
        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
        <div>
          <strong><?= $trashedRecipes ?> recipe<?= $trashedRecipes > 1 ? 's' : '' ?> in trash</strong>
          <span style="font-size:.82rem; margin-left:8px; color:var(--gray-500);">Will be permanently deleted after 30 days —</span>
          <a href="/admin/recipes/trash" style="font-size:.82rem; color:var(--pink); font-weight:600;">View trash</a>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($archivedUsers > 0): ?>
      <div class="alert alert-error" style="display:flex; align-items:center; gap:10px;">
        <span class="material-symbols-outlined" style="font-size:18px;">person_off</span>
        <div>
          <strong><?= $archivedUsers ?> archived user<?= $archivedUsers > 1 ? 's' : '' ?></strong>
          <span style="font-size:.82rem; margin-left:8px; color:var(--gray-500);">— </span>
          <a href="/admin/users/trash" style="font-size:.82rem; color:var(--pink); font-weight:600;">View archived</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="admin-container">

      <!-- Quick Actions -->
      <div style="background:var(--white); border:1px solid var(--gray-100); border-radius:var(--radius-lg); padding:20px;">
        <p style="font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--gray-400); margin-bottom:16px;">Quick Actions</p>
        <div style="display:flex; flex-direction:column; gap:8px;">
          <?php
          $actions = [
            ['/admin/recipes', 'restaurant_menu', '#EEEDFE', '#534AB7', 'Manage Recipes',   'Edit, delete, restore'],
            ['/admin/users',   'manage_accounts', '#E1F5EE', '#0F6E56', 'Manage Users',     'Promote, archive, restore'],
            ['/admin/recipes/trash', 'delete',    '#FAECE7', '#993C1D', 'Recipe Trash',     $trashedRecipes . ' item' . ($trashedRecipes !== 1 ? 's' : '') . ' pending'],
            ['/admin/users/trash',   'person_off','#FAEEDA', '#854F0B', 'Archived Users',   $archivedUsers . ' account' . ($archivedUsers !== 1 ? 's' : '') . ' pending'],
            ['/add-recipe',    'add_circle',      '#E6F1FB', '#185FA5', 'Add Recipe',       'Create a new curated recipe'],
          ];
          foreach ($actions as [$href, $icon, $bg, $color, $label, $sub]):
          ?>
          <a href="<?= $href ?>" style="display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--gray-100); text-decoration:none; color:var(--black); transition:background .15s;">
            <div style="width:34px; height:34px; border-radius:var(--radius-md);  display:grid; place-items:center; flex-shrink:0;">
              <span class="material-symbols-outlined" style="font-size:18px; color:var(--pink);"><?= $icon ?></span>
            </div>
            <div style="flex:1;">
              <div style="font-size:.88rem; font-weight:600;"><?= $label ?></div>
              <div style="font-size:.75rem; color:var(--gray-500);"><?= $sub ?></div>
            </div>
            <span class="material-symbols-outlined" style="font-size:16px; color:var(--gray-300);">arrow_forward</span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Charts -->
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; width: 100%">
        <!-- Difficulty breakdown -->
        <div style="background:var(--white); border:1px solid var(--gray-100); border-radius:var(--radius-lg); padding:20px;">
          <p style="font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--gray-400); margin-bottom:14px;">Recipes by difficulty</p>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <?php
            $diffBars = [
              ['Easy',         $easyCount,  '#1D9E75', $maxDiff],
              ['Intermediate', $interCount, '#BA7517', $maxDiff],
              ['Hard',         $hardCount,  '#A32D2D', $maxDiff],
            ];
            foreach ($diffBars as [$lbl, $val, $color, $max]):
              $pct = $max > 0 ? round($val / $max * 100) : 0;
            ?>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:.75rem; color:var(--gray-500); width:80px; text-align:right; flex-shrink:0;"><?= $lbl ?></span>
              <div style="flex:1; height:6px; background:var(--gray-100); border-radius:999px; overflow:hidden;">
                <div style="width:<?= $pct ?>%; height:100%; background:<?= $color ?>; border-radius:999px; transition:width .4s;"></div>
              </div>
              <span style="font-size:.75rem; color:var(--gray-500); width:20px; text-align:right;"><?= $val ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Type breakdown -->
        <div style="background:var(--white); border:1px solid var(--gray-100); border-radius:var(--radius-lg); padding:20px;">
          <p style="font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--gray-400); margin-bottom:14px;">Recipe types</p>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <?php
            $typeBars = [
              ['Curated',  $curatedCount,  '#534AB7', $maxType],
              ['Original', $originalCount, '#0F6E56', $maxType],
              ['Remix',    $remixCount,    '#993C1D', $maxType],
            ];
            foreach ($typeBars as [$lbl, $val, $color, $max]):
              $pct = $max > 0 ? round($val / $max * 100) : 0;
            ?>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:.75rem; color:var(--gray-500); width:80px; text-align:right; flex-shrink:0;"><?= $lbl ?></span>
              <div style="flex:1; height:6px; background:var(--gray-100); border-radius:999px; overflow:hidden;">
                <div style="width:<?= $pct ?>%; height:100%; background:<?= $color ?>; border-radius:999px;"></div>
              </div>
              <span style="font-size:.75rem; color:var(--gray-500); width:20px; text-align:right;"><?= $val ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>