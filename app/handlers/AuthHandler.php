<?php

class AuthHandler {

    public static function handleLogin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            $_SESSION['auth_error'] = 'Please fill in all fields.';
            header('Location: /login'); exit;
        }

        $userModel = new UserModel();
        $user      = $userModel->findByEmail($email);

        // TEMP DEBUG
        // var_dump($user['is_admin']); exit;

        if (!$user || !password_verify($password, $user['user_password'])) {
            $_SESSION['auth_error'] = 'Invalid email or password.';
            header('Location: /login'); exit;
        }

        if ($user['status'] === 'archived') {
            $_SESSION['archived_user_id'] = $user['user_id'];
            header('Location: /account/archived'); exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name']  = $user['last_name'];
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['is_admin']   = (bool) $user['is_admin'];

        header('Location: /recipes'); exit;
    }

    public static function handleRegister(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $email     = trim($_POST['email']      ?? '');
        $password  = trim($_POST['password']   ?? '');
        $confirm   = trim($_POST['confirm']    ?? '');

        if (!$firstName || !$lastName || !$email || !$password) {
            $_SESSION['auth_error'] = 'Please fill in all fields.';
            header('Location: /register'); exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = 'Please enter a valid email address.';
            header('Location: /register'); exit;
        }

        if ($password !== $confirm) {
            $_SESSION['auth_error'] = 'Passwords do not match.';
            header('Location: /register'); exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['auth_error'] = 'Password must be at least 6 characters.';
            header('Location: /register'); exit;
        }

        $userModel = new UserModel();

        if ($userModel->findByEmail($email)) {
            $_SESSION['auth_error'] = 'An account with that email already exists.';
            header('Location: /register'); exit;
        }

        $userModel->create($firstName, $lastName, $email, $password);
        $user = $userModel->findByEmail($email);

        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name']  = $user['last_name'];
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['is_admin']   = false;

        header('Location: /recipes'); exit;
    }
}