<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$job_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company || $job_id <= 0) {
    redirect('/Uniworksmohinhhoa/company/manage_job.php');
}

$stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?");
$stmt->execute([$job_id, $company['id']]);

setFlash('success', 'Job deleted successfully.');
redirect('/Uniworksmohinhhoa/company/manage_job.php');