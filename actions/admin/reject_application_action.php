<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireRole('admin');

$application_id = (int)($_POST['application_id'] ?? 0);

if ($application_id <= 0) {
    setFlash('error', 'Invalid application ID.');
    redirect('/Uniworksmohinhhoa/admin/applications.php');
}

// Kiểm tra application tồn tại và chưa được admin xử lý
$stmt = $pdo->prepare("SELECT id, admin_approved FROM applications WHERE id = ?");
$stmt->execute([$application_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    setFlash('error', 'Application not found.');
    redirect('/Uniworksmohinhhoa/admin/applications.php');
}

if ((int)$app['admin_approved'] !== 0) {
    setFlash('error', 'This application has already been reviewed by admin.');
    redirect('/Uniworksmohinhhoa/admin/applications.php');
}

$pdo->prepare("
    UPDATE applications
    SET admin_approved = -1, status = 'rejected'
    WHERE id = ?
")->execute([$application_id]);

setFlash('success', 'Application rejected.');
$back = $_POST['redirect_back'] ?? '/Uniworksmohinhhoa/admin/applications.php';
if (!preg_match('#^/Uniworksmohinhhoa/#', $back)) $back = '/Uniworksmohinhhoa/admin/applications.php';
redirect($back);
