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

    // Kiểm tra application tồn tại
    $stmt = $pdo->prepare("
        SELECT
            id,
            admin_approved,
            status
        FROM applications
        WHERE id = ?
    ");

    $stmt->execute([$application_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        setFlash('error', 'Application not found.');
        redirect('/Uniworksmohinhhoa/admin/applications.php');
    }

    // Không cho admin duyệt lại
    if ((int)$application['admin_approved'] !== 0) {
        setFlash('error', 'This application has already been reviewed.');
        redirect('/Uniworksmohinhhoa/admin/applications.php');
    }

    // Admin approve
    $stmt = $pdo->prepare("
        UPDATE applications
        SET
            admin_approved = 1,
            status = 'approved'
        WHERE id = ?
    ");

    $stmt->execute([$application_id]);

    setFlash(
        'success',
        'Application approved successfully. Waiting for company review.'
    );

} catch (Exception $e) {

    setFlash(
        'error',
        'Approval failed: ' . $e->getMessage()
    );
}

redirect('/Uniworksmohinhhoa/admin/applications.php');