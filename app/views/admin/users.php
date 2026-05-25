<?php
// app/views/admin/users.php
$pageTitle = 'Admin — Users';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
$searchVal = htmlspecialchars($_GET['search'] ?? '');

function adminUsersUrl(array $overrides = []): string {
    global $search, $currentPage;
    $params = [];
    $s = $overrides['search'] ?? $search;
    $p = $overrides['page']   ?? $currentPage;
    if ($s !== '') $params['search'] = $s;
    if ($p > 1)   $params['page']   = $p;
    return '/admin/users' . ($params ? '?' . http_build_query($params) : '');
}
?>

<main>
  <div class="container">

    <div class="page-header">
      <div>
        <a href="/admin" style="font-size:.85rem; color:var(--pink);">← Dashboard</a>
        <h1 style="margin-top:8px;">Manage <span class="accent">Users</span></h1>
        <p>
          Showing <?= count($users) ?> of <?= $total ?> user<?= $total !== 1 ? 's' : '' ?>
          <?= $search !== '' ? ' for "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
        </p>
      </div>
      <?php if ($archivedCount > 0): ?>
      <a href="/admin/users/trash" class="btn btn-ghost btn-sm" style="position:relative; margin-left:auto; align-self:flex-start; margin-top:8px;">
        <span class="material-symbols-outlined">person_off</span>
        Archived
        <span class="trash-badge"><?= $archivedCount ?></span>
      </a>
      <?php else: ?>
      <a href="/admin/users/trash" class="btn btn-ghost btn-sm" style="margin-left:auto; align-self:flex-start; margin-top:8px;">
        <span class="material-symbols-outlined">person_off</span>
        Archived
      </a>
      <?php endif; ?>
    </div>

    <!-- Search -->
    <form method="GET" action="/admin/users" class="account-search-form" style="margin-bottom:24px;">
      <div class="search-pill">
        <span class="material-symbols-outlined search-icon" style="font-size:18px;">search</span>
        <input type="text" name="search" value="<?= $searchVal ?>"
               placeholder="Search by name or email...">
        <?php if ($searchVal): ?>
          <a href="/admin/users" class="search-clear">
            <span class="material-symbols-outlined" style="font-size:16px;">close</span>
          </a>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Search</button>
    </form>

    <?php if (empty($users)): ?>
      <div class="account-empty" style="margin-bottom:80px;">
        <h3>No users found</h3>
        <?php if ($search !== ''): ?>
          <p>No users match your search.</p>
          <a href="/admin/users" class="btn btn-outline btn-sm">Clear search</a>
        <?php endif; ?>
      </div>
    <?php else: ?>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Recipes</th>
            <th>Admin</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u):
            $isSelf = (int)$u['user_id'] === (int)$_SESSION['user_id'];
          ?>
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div class="admin-avatar"><?= strtoupper($u['first_name'][0]) ?></div>
                <span style="font-weight:500;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></span>
                <?php if ($isSelf): ?>
                  <span class="admin-you-badge">You</span>
                <?php endif; ?>
                <?php if ($u['is_admin']): ?>
                  <span class="admin-you-badge" style="background:var(--pink-pale); color:var(--pink-dark);">Admin</span>
                <?php endif; ?>
              </div>
            </td>
            <td style="color:var(--gray-500); font-size:.85rem;">
              <?= htmlspecialchars($u['user_email']) ?>
            </td>
            <td><?= $u['recipe_count'] ?></td>
            <td>
              <?php if (!$isSelf): ?>
              <form method="POST" action="/admin/user/toggle-admin" style="margin:0;">
                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm">
                  <?= $u['is_admin'] ? 'Revoke' : 'Make Admin' ?>
                </button>
              </form>
              <?php else: ?>
                <span style="color:var(--gray-400); font-size:.8rem;">—</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--gray-500); font-size:.82rem; white-space:nowrap;">
              <?= date('M j, Y', strtotime($u['created_at'])) ?>
            </td>
            <td>
              <?php if (!$isSelf): ?>
              <form method="POST" action="/admin/user/delete" style="margin:0;">
                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="page" value="<?= $currentPage ?>">
                <button type="submit" class="btn btn-delete btn-sm" title="Archive user">
                  <span class="material-symbols-outlined">person_remove</span>
                </button>
              </form>
              <?php endif; ?>
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
        <a href="<?= adminUsersUrl(['page' => $currentPage - 1]) ?>" class="page-btn">
          <span class="material-symbols-outlined">chevron_left</span>
        </a>
      <?php else: ?>
        <span class="page-btn disabled"><span class="material-symbols-outlined">chevron_left</span></span>
      <?php endif; ?>

      <?php
      $start = max(1, $currentPage - 2);
      $end   = min($totalPages, $currentPage + 2);
      if ($start > 1): ?>
        <a href="<?= adminUsersUrl(['page' => 1]) ?>" class="page-btn">1</a>
        <?php if ($start > 2): ?><span class="page-dots">...</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i === $currentPage): ?>
          <span class="page-btn active"><?= $i ?></span>
        <?php else: ?>
          <a href="<?= adminUsersUrl(['page' => $i]) ?>" class="page-btn"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?><span class="page-dots">...</span><?php endif; ?>
        <a href="<?= adminUsersUrl(['page' => $totalPages]) ?>" class="page-btn"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($currentPage < $totalPages): ?>
        <a href="<?= adminUsersUrl(['page' => $currentPage + 1]) ?>" class="page-btn">
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