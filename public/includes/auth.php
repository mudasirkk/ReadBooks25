<?php
session_start();

function require_login() {
    if (!isset($_SESSION['username'])) {
        header("Location: /login.php");
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        echo "<h3>Access denied: you are not a(n) $role.</h3>";
        exit;
    }
}
