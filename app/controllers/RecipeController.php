<?php

require_once ROOT . '/app/models/RecipeModel.php';
require_once ROOT . '/app/models/RatingModel.php';
require_once ROOT . '/app/middleware/AuthMiddleware.php';

class RecipeController {

    public function index(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $model      = new RecipeModel();
    $page       = max(1, (int) ($_GET['page']       ?? 1));
    $search     = trim($_GET['search']              ?? '');
    $difficulty = trim($_GET['difficulty']          ?? '');
    $filter     = trim($_GET['filter']              ?? '');
    $minStars   = (int) ($_GET['stars']             ?? 0);
    $stars = isset($_GET['stars']) && $_GET['stars'] !== '' ? (int)$_GET['stars'] : null;
    $data  = $model->getPaginated($page, $search, $difficulty, $filter, $stars);
    $recipes     = $data['recipes'];
    $totalPages  = $data['total_pages'];
    $total       = $data['total'];
    $currentPage = $data['current_page'];

    require ROOT . '/app/views/recipes/index.php';
}

    public function show(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id    = (int) ($_GET['id'] ?? 0);
        $model = new RecipeModel();
        $recipe = $model->getById($id);

        if (!$recipe || $recipe['is_deleted']) {
            http_response_code(404);
            echo '<h1>Recipe not found</h1>'; return;
        }

        $ingredients  = $model->getIngredients($id);
        $directions   = $model->getDirections($id);

        $ratingModel  = new RatingModel();
        $ratings      = $ratingModel->getByRecipe($id);
        $userRating   = null;

        if (!empty($_SESSION['user_id'])) {
            $userRating = $ratingModel->getByUser((int)$_SESSION['user_id'], $id);
        }

        $hasRated     = !empty($userRating);
        $ratingError   = $_SESSION['rating_error']   ?? null;
        $ratingSuccess = $_SESSION['rating_success'] ?? null;
        unset($_SESSION['rating_error'], $_SESSION['rating_success']);

        require ROOT . '/app/views/recipes/show.php';
    }

    public function add(): void {
        AuthMiddleware::require();
        $error = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);
        require ROOT . '/app/views/recipes/add.php';
    }

    public function store(): void {
        AuthMiddleware::require();
        require_once ROOT . '/app/handlers/RecipeHandler.php';
        RecipeHandler::handleCreate();
    }

    public function grocery(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id    = (int) ($_GET['id'] ?? 0);
        $model = new RecipeModel();
        $recipe = $model->getById($id);

        if (!$recipe) {
            http_response_code(404);
            echo '<h1>Recipe not found</h1>'; return;
        }

        $ingredients = $model->getIngredients($id);
        require ROOT . '/app/views/recipes/grocery.php';
    }

    public function remix(): void {
        AuthMiddleware::require();

        $id     = (int) ($_GET['id'] ?? 0);
        $model  = new RecipeModel();
        $recipe = $model->getById($id);

        if (!$recipe) {
            http_response_code(404); echo '<h1>Recipe not found</h1>'; return;
        }

        // Block self-remix
        if ((int)$recipe['user_id'] === (int)$_SESSION['user_id']) {
            header('Location: /recipe?id=' . $id); exit;
        }

        // Block remix if not yet rated — unless it's a premade recipe
        if (!$recipe['is_premade']) {
            require_once ROOT . '/app/handlers/RatingHandler.php';
            $hasRated = RatingHandler::validateBeforeRemix((int)$_SESSION['user_id'], $id);

            if (!$hasRated) {
                $_SESSION['remix_blocked'] = 'You must rate this recipe before remixing it.';
                header('Location: /recipe?id=' . $id); exit;
            }
        }

        $ingredients = $model->getIngredients($id);
        $directions  = $model->getDirections($id);
        $error       = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);

        require ROOT . '/app/views/recipes/remix.php';
    }

    public function storeRemix(): void {
        AuthMiddleware::require();
        require_once ROOT . '/app/handlers/RecipeHandler.php';
        RecipeHandler::handleRemix();
    }

    public function rate(): void {
        AuthMiddleware::require();
        require_once ROOT . '/app/handlers/RatingHandler.php';
        RatingHandler::handleRate();
    }

    public function deleteRating(): void {
        AuthMiddleware::require();
        require_once ROOT . '/app/handlers/RatingHandler.php';
        RatingHandler::handleDelete();
    }
}