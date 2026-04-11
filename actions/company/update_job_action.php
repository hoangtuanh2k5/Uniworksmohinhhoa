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

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$userId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

$jobId = (int)($_POST['job_id'] ?? 0);
$periodId = (int)($_POST['period_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$requirements = trim($_POST['requirements'] ?? '');
$slots = (int)($_POST['slots'] ?? 1);
$deadline = $_POST['deadline'] ?? '';
$status = sanitize($_POST['status'] ?? 'open');

if (!$company || !$jobId || !$periodId || !$title || !$description || !$deadline) {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/company/manage_jobs.php');
}

try {
    $stmt = $pdo->prepare("
        UPDATE jobs
        SET period_id = ?, title = ?, description = ?, requirements = ?, slots = ?, deadline = ?, status = ?
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([
        $periodId,
        $title,
        $description,
        $requirements,
        $slots,
        $deadline,
        $status,
        $jobId,
        $company['id']
    ]);

    setFlash('success', 'Job updated successfully.');
    redirect('/Uniworksmohinhhoa/company/manage_jobs.php');
} catch (Exception $e) {
    setFlash('error', 'Failed to update job.');
    redirect('/Uniworksmohinhhoa/company/manage_jobs.php');
}