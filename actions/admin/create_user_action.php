<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|-------------------------------------------------------
| Chỉ admin mới được tạo user
|-------------------------------------------------------
*/
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$role = trim($_POST['role'] ?? '');

if ($full_name === '' || $email === '' || $password === '' || $role === '') {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/admin/create_user.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Invalid email format.');
    redirect('/Uniworksmohinhhoa/admin/create_user.php');
}

$allowedRoles = ['admin', 'student', 'company'];
if (!in_array($role, $allowedRoles, true)) {
    setFlash('error', 'Invalid role selected.');
    redirect('/Uniworksmohinhhoa/admin/create_user.php');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        setFlash('error', 'This email already exists.');
        redirect('/Uniworksmohinhhoa/admin/create_user.php');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, full_name, phone, role)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$email, $hashedPassword, $full_name, $phone, $role]);

    setFlash('success', 'User created successfully.');
    redirect('/Uniworksmohinhhoa/admin/users.php');

} catch (Exception $e) {
    die('Create user error: ' . $e->getMessage());
}