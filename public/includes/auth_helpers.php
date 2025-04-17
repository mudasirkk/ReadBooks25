<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('require_role')) {
    function require_role($role) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
            header("Location: ../public/login.php");
            exit;
        }
    }
}

if (!function_exists('require_any_role')) {
    function require_any_role(...$roles) {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
            header("Location: ../public/login.php");
            exit;
        }
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['role']);
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('is_expert')) {
    function is_expert() {
        return $_SESSION['role'] === 'expert';
    }
}

if (!function_exists('is_user')) {
    function is_user() {
        return $_SESSION['role'] === 'user';
    }
}
