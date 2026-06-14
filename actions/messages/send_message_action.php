<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$sender_id   = (int)$_SESSION['user']['id'];
$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$content     = trim($_POST['content'] ?? '');
$redirect_to = $_POST['redirect_to'] ?? '/Uniworksmohinhhoa/public/login.php';

// Chỉ cho phép redirect trong cùng domain
if (!preg_match('#^/Uniworksmohinhhoa/#', $redirect_to)) {
    $redirect_to = '/Uniworksmohinhhoa/public/login.php';
}

if ($receiver_id <= 0 || $content === '') {
    setFlash('error', 'Message cannot be empty.');
    redirect($redirect_to);
}

// Kiểm tra receiver tồn tại
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$receiver_id]);
if (!$stmt->fetch()) {
    setFlash('error', 'Recipient not found.');
    redirect($redirect_to);
}

$pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)")
    ->execute([$sender_id, $receiver_id, $content]);

redirect($redirect_to);
