<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/admin/users.php');
}

$id      = (int)($_POST['id'] ?? 0);
$name    = sanitize($_POST['full_name'] ?? '');
$email   = sanitize($_POST['email'] ?? '');
$role    = $_POST['role'] ?? 'student';
$new     = $_POST['new_password']     ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($id <= 0 || $name === '' || $email === '') {
    setFlash('error', 'Missing required fields.');
    redirect('/Uniworksmohinhhoa/admin/edit_user.php?id=' . $id);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Invalid email format.');
    redirect('/Uniworksmohinhhoa/admin/edit_user.php?id=' . $id);
}

if (!in_array($role, ['student', 'company', 'admin'], true)) {
    $role = 'student';
}

$pdo->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE id=?")
    ->execute([$name, $email, $role, $id]);

if ($new !== '') {
    if ($new !== $confirm) {
        setFlash('error', 'Passwords do not match. Other details were saved.');
        redirect('/Uniworksmohinhhoa/admin/edit_user.php?id=' . $id);
    }
    if (strlen($new) < 6) {
        setFlash('error', 'Password must be at least 6 characters. Other details were saved.');
        redirect('/Uniworksmohinhhoa/admin/edit_user.php?id=' . $id);
    }
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $id]);
}

setFlash('success', 'User updated successfully.');
redirect('/Uniworksmohinhhoa/admin/users.php');
