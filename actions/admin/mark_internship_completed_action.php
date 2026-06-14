<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/admin/monitoring.php');
}

$registration_id = (int)($_POST['registration_id'] ?? 0);

if ($registration_id <= 0) {
    setFlash('error', 'Invalid registration ID.');
    redirect('/Uniworksmohinhhoa/admin/monitoring.php');
}

$stmt = $pdo->prepare("SELECT id, status FROM internship_registrations WHERE id = ?");
$stmt->execute([$registration_id]);
$reg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reg) {
    setFlash('error', 'Internship registration not found.');
    redirect('/Uniworksmohinhhoa/admin/monitoring.php');
}

if ($reg['status'] === 'completed') {
    setFlash('error', 'This internship is already marked as completed.');
    redirect('/Uniworksmohinhhoa/admin/monitoring.php');
}

$pdo->prepare("UPDATE internship_registrations SET status = 'completed' WHERE id = ?")
    ->execute([$registration_id]);

setFlash('success', 'Internship marked as completed.');
redirect('/Uniworksmohinhhoa/admin/monitoring.php');
