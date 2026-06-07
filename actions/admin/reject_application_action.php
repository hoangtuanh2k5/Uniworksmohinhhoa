<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

requireRole('admin');

$application_id = (int)($_POST['application_id'] ?? 0);

if ($application_id <= 0) {
    setFlash('error', 'Invalid application ID.');
    redirect('/Uniworksmohinhhoa/admin/applications.php');
}

try {
    // Lấy thông tin application
    $stmt = $pdo->prepare("
        SELECT id, admin_approved
        FROM applications
        WHERE id = ?
    ");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        setFlash('error', 'Application not found.');
        redirect('/Uniworksmohinhhoa/admin/applications.php');
    }

    // Nếu đã duyệt rồi thì không cho duyệt lại
    if ((int)$application['admin_approved'] !== 0) {
        setFlash('error', 'This application has already been reviewed.');
        redirect('/Uniworksmohinhhoa/admin/applications.php');
    }

    // Reject
    $stmt = $pdo->prepare("
        UPDATE applications
        SET admin_approved = -1,
            status = 'rejected'
        WHERE id = ?
    ");
    $stmt->execute([$application_id]);

    setFlash('success', 'Application rejected successfully.');

} catch (Exception $e) {
    die('Reject error: ' . $e->getMessage());
}

redirect('/Uniworksmohinhhoa/admin/applications.php');