<?php

require_once ROOT . '/app/models/RecipeModel.php';
require_once ROOT . '/app/models/UserModel.php';
require_once ROOT . '/app/models/RatingModel.php';
require_once ROOT . '/app/middleware/AuthMiddleware.php';

class AdminController {

    private const PER_PAGE_RECIPES = 10;
    private const PER_PAGE_USERS   = 10;

    private function requireAdmin(): void {
        AuthMiddleware::require();
        if (empty($_SESSION['is_admin'])) {
            http_response_code(403);
            echo '<h1>Access Denied</h1>'; exit;
        }
    }

    public function index(): void {
    $this->requireAdmin();

    $recipeModel   = new RecipeModel();
    $userModel     = new UserModel();
    $allRecipes    = $recipeModel->getAllForAdmin();

    $totalRecipes  = count(array_filter($allRecipes, fn($r) => !$r['is_deleted']));
    $totalUsers    = count($userModel->getActiveUsers());
    $trashedRecipes = count(array_filter($allRecipes, fn($r) => $r['is_deleted']));
    $archivedUsers = $userModel->getArchivedCount();

    require ROOT . '/app/views/admin/index.php';
}

    public function users(): void {
        $this->requireAdmin();

        $userModel   = new UserModel();
        $search      = trim($_GET['search'] ?? '');
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $perPage     = self::PER_PAGE_USERS;

        $allUsers    = $userModel->getActiveUsers();

        if ($search !== '') {
            $allUsers = array_filter($allUsers, fn($u) =>
                stripos($u['first_name'] . ' ' . $u['last_name'], $search) !== false ||
                stripos($u['user_email'], $search) !== false
            );
            $allUsers = array_values($allUsers);
        }

        $total       = count($allUsers);
        $totalPages  = max(1, (int) ceil($total / $perPage));
        $offset      = ($page - 1) * $perPage;
        $users       = array_slice($allUsers, $offset, $perPage);
        $currentPage = $page;

        $archivedCount = $userModel->getArchivedCount();

        require ROOT . '/app/views/admin/users.php';
    }

    public function trashedUsers(): void {
        $this->requireAdmin();

        $userModel = new UserModel();
        $users     = $userModel->getArchivedUsers();

        require ROOT . '/app/views/admin/trashed-users.php';
    }

    public function recipes(): void {
        $this->requireAdmin();

        $recipeModel = new RecipeModel();
        $search      = trim($_GET['search'] ?? '');
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $perPage     = self::PER_PAGE_RECIPES;

        $allRecipes  = $recipeModel->getActiveForAdmin();

        if ($search !== '') {
            $allRecipes = array_filter($allRecipes, fn($r) =>
                stripos($r['title'], $search) !== false ||
                stripos($r['first_name'] . ' ' . $r['last_name'], $search) !== false
            );
            $allRecipes = array_values($allRecipes);
        }

        $total       = count($allRecipes);
        $totalPages  = max(1, (int) ceil($total / $perPage));
        $offset      = ($page - 1) * $perPage;
        $recipes     = array_slice($allRecipes, $offset, $perPage);
        $currentPage = $page;

        $trashedCount = $recipeModel->getTrashedCountAdmin();

        require ROOT . '/app/views/admin/recipes.php';
    }

    public function trashedRecipes(): void {
        $this->requireAdmin();

        $recipeModel = new RecipeModel();
        $recipes     = $recipeModel->getTrashedForAdmin();

        require ROOT . '/app/views/admin/trashed-recipes.php';
    }

    public function editRecipe(): void {
        $this->requireAdmin();

        $id          = (int) ($_GET['id'] ?? 0);
        $model       = new RecipeModel();
        $recipe      = $model->getById($id);

        if (!$recipe) {
            http_response_code(404); echo '<h1>Recipe not found</h1>'; return;
        }

        $ingredients = $model->getIngredients($id);
        $directions  = $model->getDirections($id);
        $error       = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);

        require ROOT . '/app/views/admin/edit-recipe.php';
    }

    public function updateRecipe(): void {
        $this->requireAdmin();
        require_once ROOT . '/app/handlers/AdminHandler.php';
        AdminHandler::handleUpdateRecipe();
    }

    // Soft delete — moves to trash
    public function deleteRecipe(): void {
        $this->requireAdmin();

        $id     = (int) ($_POST['recipe_id'] ?? 0);
        $search = trim($_POST['search']      ?? '');
        $page   = (int) ($_POST['page']      ?? 1);

        $model  = new RecipeModel();
        $model->softDelete($id);

        $params = [];
        if ($search !== '') $params['search'] = $search;
        if ($page > 1)      $params['page']   = $page;

        header('Location: /admin/recipes' . ($params ? '?' . http_build_query($params) : '')); exit;
    }

    // Restore recipe from trash
    public function restoreRecipe(): void {
        $this->requireAdmin();

        $id    = (int) ($_POST['recipe_id'] ?? 0);
        $model = new RecipeModel();
        $model->restore($id);

        header('Location: /admin/recipes/trash'); exit;
    }

    // Permanently delete recipe
    public function permanentDeleteRecipe(): void {
        $this->requireAdmin();

        $id    = (int) ($_POST['recipe_id'] ?? 0);
        $model = new RecipeModel();
        $model->delete($id);

        header('Location: /admin/recipes/trash'); exit;
    }

    // Archive user — soft delete
    public function deleteUser(): void {
        $this->requireAdmin();

        $userId    = (int) ($_POST['user_id'] ?? 0);
        $search    = trim($_POST['search']    ?? '');
        $page      = (int) ($_POST['page']    ?? 1);

        if ($userId === (int)$_SESSION['user_id']) {
            header('Location: /admin/users'); exit;
        }

        $userModel = new UserModel();
        $userModel->archive($userId);

        $params = [];
        if ($search !== '') $params['search'] = $search;
        if ($page > 1)      $params['page']   = $page;

        header('Location: /admin/users' . ($params ? '?' . http_build_query($params) : '')); exit;
    }

    // Restore archived user
    public function restoreUser(): void {
        $this->requireAdmin();

        $userId    = (int) ($_POST['user_id'] ?? 0);
        $userModel = new UserModel();
        $userModel->restore($userId);

        header('Location: /admin/users/trash'); exit;
    }

    // Permanently delete user
    public function permanentDeleteUser(): void {
        $this->requireAdmin();

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId === (int)$_SESSION['user_id']) {
            header('Location: /admin/users/trash'); exit;
        }

        $userModel = new UserModel();
        $userModel->delete($userId);

        header('Location: /admin/users/trash'); exit;
    }

    public function toggleAdmin(): void {
        $this->requireAdmin();

        $userId    = (int) ($_POST['user_id'] ?? 0);
        $userModel = new UserModel();
        $userModel->toggleAdmin($userId);

        header('Location: /admin/users'); exit;
    }
}