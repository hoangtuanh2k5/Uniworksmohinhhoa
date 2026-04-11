<?php
require_once __DIR__ . '/../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/users.php');
    exit;
}

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = trim($_POST['role'] ?? 'student');
$allowedRoles = ['student', 'company', 'admin'];

if ($id <= 0) {
    header('Location: ../../admin/users.php?error=invalid_id');
    exit;
}

if ($fullName === '' || $email === '') {
    header('Location: ../../admin/edit_user.php?id=' . $id . '&error=missing_fields');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../../admin/edit_user.php?id=' . $id . '&error=invalid_email');
    exit;
}

if (!in_array($role, $allowedRoles, true)) {
    $role = 'student';
}

$stmt = $conn->prepare('UPDATE users SET full_name = ?, email = ?, role = ?, phone = ? WHERE id = ?');
$stmt->bind_param('ssssi', $fullName, $email, $role, $phone, $id);

if ($stmt->execute()) {
    header('Location: ../../admin/users.php?msg=updated');
    exit;
}

header('Location: ../../admin/edit_user.php?id=' . $id . '&error=update_failed');
exit;
