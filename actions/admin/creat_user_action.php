<?php
require_once __DIR__ . '/../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/create_user.php');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = trim($_POST['role'] ?? 'student');
$password = trim($_POST['password'] ?? '');
$allowedRoles = ['student', 'company', 'admin'];

if ($fullName === '' || $email === '' || $password === '') {
    header('Location: ../../admin/create_user.php?error=missing_fields');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../../admin/create_user.php?error=invalid_email');
    exit;
}

if (!in_array($role, $allowedRoles, true)) {
    $role = 'student';
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (full_name, email, phone, role, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
$stmt->bind_param('sssss', $fullName, $email, $phone, $role, $passwordHash);

if ($stmt->execute()) {
    header('Location: ../../admin/users.php?msg=created');
    exit;
}

header('Location: ../../admin/create_user.php?error=create_failed');
exit;
