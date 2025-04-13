<?php
session_start();

function require_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: ../public/login.php");
        exit;
    }
}

function require_any_role(...$roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        header("Location: ../public/login.php");
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['role']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_expert() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'expert';
}

function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}
