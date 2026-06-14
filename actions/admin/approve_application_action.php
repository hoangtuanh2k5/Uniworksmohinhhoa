<?php
<<<<<<< Updated upstream
require_once __DIR__ . '/../../includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../admin/applications.php?error=invalid_id');
    exit;
}

$appId = (int) $_GET['id'];
$conn->begin_transaction();

try {
    $checkStmt = $conn->prepare('SELECT status FROM applications WHERE id = ?');
    $checkStmt->bind_param('i', $appId);
    $checkStmt->execute();
    $application = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$application) {
        throw new RuntimeException('Application not found');
    }

    $updateStmt = $conn->prepare("UPDATE applications SET status = 'approved', admin_approved = 1 WHERE id = ?");
    $updateStmt->bind_param('i', $appId);
    $updateStmt->execute();
    $updateStmt->close();

    $registrationStmt = $conn->prepare('SELECT id FROM internship_registrations WHERE application_id = ?');
    $registrationStmt->bind_param('i', $appId);
    $registrationStmt->execute();
    $registrationExists = $registrationStmt->get_result()->num_rows > 0;
    $registrationStmt->close();

    if (!$registrationExists) {
        $insertStmt = $conn->prepare('INSERT INTO internship_registrations (application_id, start_date) VALUES (?, CURDATE())');
        $insertStmt->bind_param('i', $appId);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $conn->commit();
    header('Location: ../../admin/applications.php?msg=approved');
    exit;
} catch (Throwable $exception) {
    $conn->rollback();
    header('Location: ../../admin/applications.php?error=failed');
    exit;
}
=======
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireRole('admin');

$user = currentUser();

$application_id = (int)($_POST['application_id'] ?? 0);

if ($application_id <= 0) {
    setFlash('error', 'Invalid application ID.');
    redirect('/Uniworksmohinhhoa/admin/applications.php');
}

try {
    $stmt = $pdo->prepare("SELECT id, admin_approved FROM applications WHERE id = ?");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        setFlash('error', 'Application not found.');
        redirect('/Uniworksmohinhhoa/admin/applications.php');
    }

    if ((int)$application['admin_approved'] !== 0) {
        setFlash('error', 'This application has already been reviewed.');
        redirect('/Uniworksmohinhhoa/admin/applications.php');
    }

    $pdo->prepare("
        UPDATE applications
        SET admin_approved = 1,
            status = 'pending'
        WHERE id = ?
    ")->execute([$application_id]);

    setFlash('success', 'Application approved. Waiting for company decision.');

} catch (Exception $e) {
    setFlash('error', 'Failed to approve application.');
}

redirect('/Uniworksmohinhhoa/admin/applications.php');
>>>>>>> Stashed changes
