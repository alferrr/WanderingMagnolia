<?php
$pageTitle = 'Admin — Archived Users';
require ROOT . '/app/views/partials/head.php';
require ROOT . '/app/views/partials/navbar.php';
?>

<main>
  <div class="container">

    <div class="page-header">
      <div>
        <a href="/admin/users" style="font-size:.85rem; color:var(--pink);">← Back to Users</a>
        <h1 style="margin-top:8px;">Archived <span class="accent">Users</span></h1>
        <p><?= count($users) ?> archived user<?= count($users) !== 1 ? 's' : '' ?></p>
      </div>
    </div>

    <?php if (empty($users)): ?>
      <div class="account-empty" style="margin-bottom:80px;">
        <span class="material-symbols-outlined" style="font-size:3rem; color:var(--gray-300); display:block; margin-bottom:16px;">person_off</span>
        <h3>No archived users</h3>
        <p>No users have been archived.</p>
        <a href="/admin/users" class="btn btn-outline btn-sm" style="margin-top:8px;">Back to Users</a>
      </div>
    <?php else: ?>

    <div class="admin-table-wrap" style="margin-bottom:80px;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Recipes</th>
            <th>Archived</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr style="opacity:.75;">
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div class="admin-avatar" style="filter:grayscale(60%); opacity:.7;">
                  <?= strtoupper($u['first_name'][0]) ?>
                </div>
                <span style="font-weight:500; color:var(--gray-500);">
                  <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                </span>
              </div>
            </td>
            <td style="color:var(--gray-500); font-size:.85rem;">
              <?= htmlspecialchars($u['user_email']) ?>
            </td>
            <td><?= $u['recipe_count'] ?></td>
            <td style="color:var(--gray-500); font-size:.82rem; white-space:nowrap;">
              <?php if ($u['archived_at']): ?>
                <?= date('M j, Y', strtotime($u['archived_at'])) ?>
                <?php
                  $archivedAt = new DateTime($u['archived_at']);
                  $expiresAt  = (clone $archivedAt)->modify('+30 days');
                  $now        = new DateTime();
                  $daysLeft   = max(0, (int)$now->diff($expiresAt)->days);
                ?>
                <div class="trash-expiry <?= $daysLeft <= 3 ? 'urgent' : '' ?>" style="margin-top:4px; display:inline-flex;">
                  <!-- <span class="material-symbols-outlined" style="font-size:11px;">schedule</span>
                  <?= $daysLeft === 0 ? 'Expires today' : ($daysLeft === 1 ? '1 day left' : $daysLeft . ' days left') ?>
                </div> -->
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <!-- Restore -->
                <form method="POST" action="/admin/user/restore" style="margin:0;">
                  <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm" title="Restore user">
                    <span class="material-symbols-outlined">person_check</span>
                    Restore
                  </button>
                </form>
                <!-- Permanent delete -->
                <!-- <form method="POST" action="/admin/user/delete-permanent" style="margin:0;">
                  <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                  <button type="submit" class="btn btn-delete btn-sm" title="Delete permanently">
                    <span class="material-symbols-outlined">delete_forever</span>
                  </button>
                </form> -->
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