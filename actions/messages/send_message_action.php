<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$sender_id = $_SESSION['user']['id'];
$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
$redirect_to = $_POST['redirect_to'] ?? '/Uniworksmohinhhoa/public/index.php';

if ($receiver_id <= 0 || $content === '') {
    setFlash('error', 'Invalid message.');
    redirect($redirect_to);
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, content)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$sender_id, $receiver_id, $content]);

    setFlash('success', 'Message sent successfully.');
    redirect($redirect_to);

} catch (Exception $e) {
    setFlash('error', 'Failed to send message.');
    redirect($redirect_to);
}