<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$job_id = (int)($_POST['job_id'] ?? 0);

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

try {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?");
    $stmt->execute([$job_id, $company['id']]);

    setFlash('success', 'Job deleted successfully.');
    redirect('/Uniworksmohinhhoa/company/manage_job.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to delete job.');
    redirect('/Uniworksmohinhhoa/company/manage_job.php');
}