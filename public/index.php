<?php
// public/index.php

define('ROOT', dirname(__DIR__));

if ($_SERVER['REQUEST_URI'] === '/health') {
    http_response_code(200); echo 'OK'; exit;
}

session_start();

require_once ROOT . '/app/core/Database.php';
require_once ROOT . '/app/core/Router.php';

$router = new Router();

// Home & Auth
$router->get('/',                    'HomeController',           'index');
$router->get('/about',               'HomeController',           'about');
$router->get('/coming-soon',         'HomeController',           'comingSoon');
$router->get('/login',               'AuthController',           'showLogin');
$router->post('/login',              'AuthController',           'login');
$router->get('/register',            'AuthController',           'showRegister');
$router->post('/register',           'AuthController',           'register');
$router->get('/logout',              'AuthController',           'logout');
$router->get('/about', 'HomeController', 'about');

// Forgot Password
$router->get('/forgot-password',     'ForgotPasswordController', 'showRequest');
$router->post('/forgot-password',    'ForgotPasswordController', 'sendOtp');
$router->get('/verify-otp',          'ForgotPasswordController', 'showVerify');
$router->post('/verify-otp',         'ForgotPasswordController', 'verifyOtp');
$router->get('/reset-password',      'ForgotPasswordController', 'showReset');
$router->post('/reset-password',     'ForgotPasswordController', 'resetPassword');

// Recipes
$router->get('/recipes',             'RecipeController',         'index');
$router->get('/recipe',              'RecipeController',         'show');
$router->get('/add-recipe',          'RecipeController',         'add');
$router->post('/add-recipe',         'RecipeController',         'store');
$router->get('/grocery',             'RecipeController',         'grocery');
$router->get('/remix-recipe',        'RecipeController',         'remix');
$router->post('/remix-recipe',       'RecipeController',         'storeRemix');
$router->post('/recipe/rate',        'RecipeController',         'rate');
$router->post('/recipe/delete-rating', 'RecipeController',       'deleteRating');

// Account
$router->get('/account',                   'AccountController',  'index');
$router->get('/account/trash',             'AccountController',  'trash');
$router->get('/account/settings',          'AccountController',  'settings');
$router->post('/account/profile',          'AccountController',  'updateProfile');
$router->post('/account/password',         'AccountController',  'changePassword');
$router->post('/account/archive',          'AccountController',  'archive');
$router->get('/account/archived',          'AccountController',  'archived');
$router->post('/account/restore',          'AccountController',  'restoreAccount');
$router->get('/edit-recipe',               'AccountController',  'edit');
$router->post('/edit-recipe',              'AccountController',  'update');
$router->post('/delete-recipe',            'AccountController',  'delete');
$router->post('/restore-recipe',           'AccountController',  'restoreRecipe');
$router->post('/delete-recipe-permanent',  'AccountController',  'permanentDelete');

// Admin
$router->get('/admin',                     'AdminController',    'index');
$router->get('/admin/users',               'AdminController',    'users');
$router->get('/admin/recipes',             'AdminController',    'recipes');
$router->get('/admin/recipe/edit',         'AdminController',    'editRecipe');
$router->post('/admin/recipe/update',      'AdminController',    'updateRecipe');
$router->post('/admin/recipe/delete',      'AdminController',    'deleteRecipe');
$router->post('/admin/user/toggle-admin',  'AdminController',    'toggleAdmin');
$router->post('/admin/user/delete',        'AdminController',    'deleteUser');
$router->get('/admin/recipes/trash',           'AdminController', 'trashedRecipes');
$router->post('/admin/recipe/restore',          'AdminController', 'restoreRecipe');
$router->post('/admin/recipe/delete-permanent', 'AdminController', 'permanentDeleteRecipe');
$router->get('/admin/users/trash',              'AdminController', 'trashedUsers');
$router->post('/admin/user/restore',            'AdminController', 'restoreUser');
$router->post('/admin/user/delete-permanent',   'AdminController', 'permanentDeleteUser');
$router->dispatch();