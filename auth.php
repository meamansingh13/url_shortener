<?php
require_once 'config.php';

function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function require_login() {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function is_role($role) {
    $u = current_user();
    return $u && $u['role'] === $role;
}

function can_create_url() {
    $u = current_user();
    if (!$u) return false;
    // SuperAdmin cannot create URLs
    return in_array($u['role'], ['admin', 'member']);
}
