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

$title = sanitize($_POST['title'] ?? '');
$period_id = (int)($_POST['period_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$requirements = trim($_POST['requirements'] ?? '');
$slots = (int)($_POST['slots'] ?? 0);
$deadline = $_POST['deadline'] ?? '';
$status = sanitize($_POST['status'] ?? 'open');

if (!$title || $period_id <= 0 || !$description || !$requirements || $slots <= 0 || !$deadline) {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/company/edit_job.php?id=' . $job_id);
}

try {
    $stmt = $pdo->prepare("
        UPDATE jobs
        SET period_id = ?, title = ?, description = ?, requirements = ?, slots = ?, deadline = ?, status = ?
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([$period_id, $title, $description, $requirements, $slots, $deadline, $status, $job_id, $company['id']]);

    setFlash('success', 'Job updated successfully.');
    redirect('/Uniworksmohinhhoa/company/manage_job.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to update job.');
    redirect('/Uniworksmohinhhoa/company/edit_job.php?id=' . $job_id);
}