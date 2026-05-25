<?php

class RatingHandler {

    public static function handleRate(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $recipeId = (int) ($_POST['recipe_id'] ?? 0);
        $stars    = (int) ($_POST['stars']     ?? 0);
        $review   = trim($_POST['review']      ?? '');

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); exit;
        }

        if ($stars < 1 || $stars > 5 || !$recipeId) {
            $_SESSION['rating_error'] = 'Please select a star rating.';
            header('Location: /recipe?id=' . $recipeId); exit;
        }

        $userId = (int) $_SESSION['user_id'];

        // Can't rate your own recipe
        $recipeModel = new RecipeModel();
        $recipe      = $recipeModel->getById($recipeId);

        if (!$recipe) {
            header('Location: /recipes'); exit;
        }

        if ((int)$recipe['user_id'] === $userId) {
            $_SESSION['rating_error'] = 'You cannot rate your own recipe.';
            header('Location: /recipe?id=' . $recipeId); exit;
        }

        $ratingModel = new RatingModel();
        $ratingModel->upsert($userId, $recipeId, $stars, $review ?: null);

        $_SESSION['rating_success'] = 'Your rating has been saved.';
        header('Location: /recipe?id=' . $recipeId); exit;
    }

    public static function handleDelete(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $recipeId = (int) ($_POST['recipe_id'] ?? 0);

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login'); exit;
        }

        $ratingModel = new RatingModel();
        $ratingModel->delete((int)$_SESSION['user_id'], $recipeId);

        header('Location: /recipe?id=' . $recipeId); exit;
    }

    // Called before remix — validates rating exists
    public static function validateBeforeRemix(int $userId, int $recipeId): bool {
        $ratingModel = new RatingModel();
        return $ratingModel->hasRated($userId, $recipeId);
    }
}