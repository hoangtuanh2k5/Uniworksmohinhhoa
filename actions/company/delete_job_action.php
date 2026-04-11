<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/company/manage_jobs.php');
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$userId = $_SESSION['user']['id'];
$jobId = (int)($_POST['job_id'] ?? 0);

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$userId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company || !$jobId) {
    setFlash('error', 'Invalid request.');
    redirect('/Uniworksmohinhhoa/company/manage_jobs.php');
}

try {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?");
    $stmt->execute([$jobId, $company['id']]);

    setFlash('success', 'Job deleted successfully.');
    redirect('/Uniworksmohinhhoa/company/manage_jobs.php');
} catch (Exception $e) {
    setFlash('error', 'Failed to delete job.');
    redirect('/Uniworksmohinhhoa/company/manage_jobs.php');
}