<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$application_id = (int)($_POST['application_id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');

if (!in_array($status, ['approved', 'rejected'], true)) {
    setFlash('error', 'Invalid status.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

try {
    $stmt = $pdo->prepare("
        UPDATE applications a
        INNER JOIN jobs j ON a.job_id = j.id
        SET a.status = ?
        WHERE a.id = ? AND j.company_id = ?
    ");
    $stmt->execute([$status, $application_id, $company['id']]);

    setFlash('success', 'Application updated successfully.');
    redirect('/Uniworksmohinhhoa/company/applications.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to update application.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}