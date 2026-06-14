<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id   = $_SESSION['user']['id'];
$role      = $_SESSION['user']['role'];
$current   = $_POST['current_password']  ?? '';
$new       = $_POST['new_password']      ?? '';
$confirm   = $_POST['confirm_password']  ?? '';

// Xác định trang redirect về
$back = match($role) {
    'student' => '/Uniworksmohinhhoa/student/profile.php',
    'company' => '/Uniworksmohinhhoa/company/profile.php',
    default   => '/Uniworksmohinhhoa/public/login.php',
};

if (empty($current) || empty($new) || empty($confirm)) {
    setFlash('error', 'Please fill in all password fields.');
    redirect($back);
}

if ($new !== $confirm) {
    setFlash('error', 'New passwords do not match.');
    redirect($back);
}

if (strlen($new) < 6) {
    setFlash('error', 'New password must be at least 6 characters.');
    redirect($back);
}

// Lấy hash hiện tại
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || !password_verify($current, $row['password'])) {
    setFlash('error', 'Current password is incorrect.');
    redirect($back);
}

// Update
$hash = password_hash($new, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user_id]);

setFlash('success', 'Password changed successfully.');
redirect($back);
