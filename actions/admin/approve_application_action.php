<?php
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
