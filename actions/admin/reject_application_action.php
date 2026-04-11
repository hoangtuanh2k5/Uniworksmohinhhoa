<?php
require_once __DIR__ . '/../../includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../admin/applications.php?error=invalid_id');
    exit;
}

$appId = (int) $_GET['id'];
$stmt = $conn->prepare("UPDATE applications SET status = 'rejected', admin_approved = 0 WHERE id = ?");
$stmt->bind_param('i', $appId);

if ($stmt->execute()) {
    header('Location: ../../admin/applications.php?msg=rejected');
    exit;
}

header('Location: ../../admin/applications.php?error=failed');
exit;
