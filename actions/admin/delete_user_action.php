<?php
require_once __DIR__ . '/../../includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../admin/users.php?error=invalid_id');
    exit;
}

$id = (int) $_GET['id'];
$stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    header('Location: ../../admin/users.php?msg=deleted');
    exit;
}

header('Location: ../../admin/users.php?error=delete_failed');
exit;
