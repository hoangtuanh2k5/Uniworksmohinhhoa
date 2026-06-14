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

/**
 * Tự động đóng các job đã quá deadline.
 * Gọi hàm này ở bất kỳ trang nào load jobs.
 * Dùng session cache để tránh chạy query quá nhiều lần trong 1 phiên.
 */
function closeExpiredJobs(PDO $pdo): void {
    // Chỉ chạy tối đa 1 lần mỗi 5 phút trong cùng session
    $now = time();
    if (isset($_SESSION['_jobs_checked']) && ($now - $_SESSION['_jobs_checked']) < 300) {
        return;
    }
    $_SESSION['_jobs_checked'] = $now;

    $pdo->prepare("
        UPDATE jobs
        SET status = 'closed'
        WHERE status = 'open'
          AND deadline < CURDATE()
    ")->execute();
}
?>