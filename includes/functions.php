<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: $path");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function isRole($role) {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === $role;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>