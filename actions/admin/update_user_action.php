<?php
<<<<<<< Updated upstream
require_once __DIR__ . '/../../includes/db_connect.php';
=======
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
>>>>>>> Stashed changes

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/users.php');
    exit;
}

<<<<<<< Updated upstream
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
=======
$id      = (int)$_POST['id'];
$name    = sanitize($_POST['full_name']);
$email   = sanitize($_POST['email']);
$role    = $_POST['role'];
$new     = $_POST['new_password']     ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// Update thông tin cơ bản
$pdo->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE id=?")
    ->execute([$name, $email, $role, $id]);

// Đổi password nếu admin có nhập
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
>>>>>>> Stashed changes
