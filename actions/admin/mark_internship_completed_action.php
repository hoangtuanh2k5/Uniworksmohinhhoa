<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireRole('admin');

$registration_id = (int)($_POST['registration_id'] ?? 0);

if ($registration_id <= 0) {
    setFlash('error', 'Invalid internship registration.');
    redirect('/Uniworksmohinhhoa/admin/monitoring.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT id, status
        FROM internship_registrations
        WHERE id = ?
    ");
    $stmt->execute([$registration_id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        setFlash('error', 'Internship registration not found.');
        redirect('/Uniworksmohinhhoa/admin/monitoring.php');
    }

    if ($registration['status'] === 'completed') {
        setFlash('error', 'This internship is already completed.');
        redirect('/Uniworksmohinhhoa/admin/monitoring.php');
    }

    $stmt = $pdo->prepare("
        UPDATE internship_registrations
        SET status = 'completed'
        WHERE id = ?
    ");
    $stmt->execute([$registration_id]);

    setFlash('success', 'Internship marked as completed successfully.');

} catch (Exception $e) {
    setFlash('error', 'Failed to update internship status: ' . $e->getMessage());
}

redirect('/Uniworksmohinhhoa/admin/monitoring.php');