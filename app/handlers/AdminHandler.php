<?php

class AdminHandler {

    public static function handleUpdateRecipe(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $recipeId   = (int) ($_POST['recipe_id'] ?? 0);
        $title      = trim($_POST['title']       ?? '');
        $difficulty = trim($_POST['difficulty']  ?? 'Easy');
        $ingNames   = $_POST['ing_name']  ?? [];
        $ingQtys    = $_POST['ing_qty']   ?? [];
        $ingUnits   = $_POST['ing_unit']  ?? [];
        $steps      = $_POST['direction'] ?? [];

        if (!$title || !$recipeId) {
            $_SESSION['form_error'] = 'Recipe title is required.';
            header('Location: /admin/recipe/edit?id=' . $recipeId); exit;
        }

        $model  = new RecipeModel();
        $recipe = $model->getById($recipeId);

        if (!$recipe) {
            http_response_code(404); echo '<h1>Recipe not found</h1>'; exit;
        }

        // Handle image upload
        $imageUrl = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid('recipe_', true) . '.' . $ext;
                $dest     = ROOT . '/public/uploads/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imageUrl = '/uploads/' . $filename;
                }
            }
        }

        $model->update($recipeId, $title, $difficulty, $imageUrl);

        $model->clearIngredients($recipeId);
        foreach ($ingNames as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;
            $model->addIngredient($recipeId, $name, (float)($ingQtys[$i] ?? 1), trim($ingUnits[$i] ?? ''));
        }

        $model->clearDirections($recipeId);
        $stepNum = 1;
        foreach ($steps as $instruction) {
            $instruction = trim($instruction);
            if ($instruction === '') continue;
            $model->addDirection($recipeId, $stepNum++, $instruction);
        }

        header('Location: /admin/recipes'); exit;
    }
}