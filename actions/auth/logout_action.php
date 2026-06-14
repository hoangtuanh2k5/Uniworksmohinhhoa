<?php
// Redirect về logout.php thay vì xử lý trực tiếp ở đây
// để tránh path resolution issues khi được include từ nhiều nơi
require_once __DIR__ . '/../../includes/functions.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

session_start();
$_SESSION['flash'] = [
    'type'    => 'success',
    'message' => 'You have been logged out successfully.'
];

header("Location: /Uniworksmohinhhoa/public/login.php");
exit;
