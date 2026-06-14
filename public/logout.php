<?php
require_once '../includes/functions.php';

// Session đã được start bởi functions.php
// Xóa toàn bộ data user
$_SESSION = [];

// Xóa cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mở session mới chỉ để giữ flash
session_start();
$_SESSION['flash'] = [
    'type'    => 'success',
    'message' => 'You have been logged out successfully.'
];

header("Location: /Uniworksmohinhhoa/public/login.php");
exit;
