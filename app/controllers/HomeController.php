<?php

class HomeController {
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user_id'])) {
            header('Location: /recipes');
            exit;
        }
        require ROOT . '/app/views/home.php';
    }
     public function comingSoon(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require ROOT . '/app/views/coming-soon.php';
    }
}
