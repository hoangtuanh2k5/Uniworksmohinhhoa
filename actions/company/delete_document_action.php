<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$doc_id  = (int)($_GET['id'] ?? 0);

// Chỉ cho xóa document của chính company mình
$stmt = $pdo->prepare("
    SELECT cd.doc_url
    FROM company_documents cd
    INNER JOIN companies c ON cd.company_id = c.id
    WHERE cd.id = ? AND c.user_id = ?
");
$stmt->execute([$doc_id, $user_id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    setFlash('error', 'Document not found.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

// Xóa file vật lý
$filePath = __DIR__ . '/../../' . $doc['doc_url'];
if (file_exists($filePath)) {
    @unlink($filePath);
}

$pdo->prepare("DELETE FROM company_documents WHERE id = ?")->execute([$doc_id]);

setFlash('success', 'Document deleted.');
redirect('/Uniworksmohinhhoa/company/profile.php');
