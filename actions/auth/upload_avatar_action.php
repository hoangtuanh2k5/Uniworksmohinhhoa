<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$role    = $_SESSION['user']['role'];

$back = match($role) {
    'student' => '/Uniworksmohinhhoa/student/profile.php',
    'company' => '/Uniworksmohinhhoa/company/profile.php',
    'admin'   => '/Uniworksmohinhhoa/admin/dashboard.php',
    default   => '/Uniworksmohinhhoa/public/login.php',
};

if (empty($_FILES['avatar']['name'])) {
    setFlash('error', 'Please select an image to upload.');
    redirect($back);
}

$file     = $_FILES['avatar'];
$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$maxSize  = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowed)) {
    setFlash('error', 'Only JPG, PNG, WEBP, GIF images are allowed.');
    redirect($back);
}

if ($file['size'] > $maxSize) {
    setFlash('error', 'Image must be under 2MB.');
    redirect($back);
}

$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
$dest     = __DIR__ . '/../../uploads/avatars/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    setFlash('error', 'Upload failed. Please try again.');
    redirect($back);
}

// Xóa avatar cũ nếu có
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$old = $stmt->fetchColumn();
if ($old && file_exists(__DIR__ . '/../../uploads/avatars/' . basename($old))) {
    @unlink(__DIR__ . '/../../uploads/avatars/' . basename($old));
}

$pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$filename, $user_id]);
$_SESSION['user']['avatar'] = $filename;

setFlash('success', 'Profile photo updated successfully.');
redirect($back);
