<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id         = $_SESSION['user']['id'];
$registration_id = (int)($_POST['registration_id'] ?? 0);

if ($registration_id <= 0) {
    setFlash('error', 'Invalid request.');
    redirect('/Uniworksmohinhhoa/company/evaluations.php');
}

// Xác minh registration thuộc company này
$stmt = $pdo->prepare("
    SELECT ir.id, ir.status
    FROM internship_registrations ir
    INNER JOIN applications a  ON ir.application_id = a.id
    INNER JOIN jobs j          ON a.job_id = j.id
    INNER JOIN companies c     ON j.company_id = c.id
    WHERE ir.id = ? AND c.user_id = ?
");
$stmt->execute([$registration_id, $user_id]);
$reg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reg) {
    setFlash('error', 'Internship not found or access denied.');
    redirect('/Uniworksmohinhhoa/company/evaluations.php');
}

if ($reg['status'] === 'completed') {
    setFlash('error', 'This internship is already completed.');
    redirect('/Uniworksmohinhhoa/company/evaluations.php');
}

$pdo->prepare("UPDATE internship_registrations SET status = 'completed' WHERE id = ?")
    ->execute([$registration_id]);

setFlash('success', 'Internship marked as completed. You can now submit an evaluation.');
redirect('/Uniworksmohinhhoa/company/evaluations.php');
