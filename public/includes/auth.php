<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('require_login')) {
    function require_login() {
        if (!isset($_SESSION['username'])) {
            header("Location: /login.php");
            exit;
        }
    }
}

if (!function_exists('require_role')) {
    function require_role($role) {
        require_login();
        if ($_SESSION['role'] !== $role) {
            echo "<h3>Access denied: you are not a(n) $role.</h3>";
            exit;
        }
    }
}
