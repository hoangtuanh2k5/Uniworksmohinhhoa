<?php
/**
 * mark_seen_action.php
 * Gọi bằng AJAX POST với param: type = messages | evaluations | reports
 * Cập nhật last_seen_* cho user hiện tại.
 */
require_once '../includes/functions.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['ok' => false]);
    exit;
}

$type    = $_POST['type'] ?? '';
$uid     = (int)$_SESSION['user']['id'];
$allowed = ['messages' => 'last_seen_messages',
            'evaluations' => 'last_seen_evaluations',
            'reports' => 'last_seen_reports'];

if (!isset($allowed[$type])) {
    echo json_encode(['ok' => false]);
    exit;
}

$col = $allowed[$type];
$pdo->prepare("UPDATE users SET $col = NOW() WHERE id = ?")->execute([$uid]);
echo json_encode(['ok' => true]);
