<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/company/manage_job.php');
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$userId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company not found.');
    redirect('/Uniworksmohinhhoa/company/manage_job.php');
}

$jobId       = (int)($_POST['job_id']       ?? 0);
$periodId    = (int)($_POST['period_id']    ?? 0);
$title       = sanitize($_POST['title']       ?? '');
$description = trim($_POST['description']     ?? '');
$requirements= trim($_POST['requirements']    ?? '');
$slots       = max(1, (int)($_POST['slots']  ?? 1));
$deadline    = $_POST['deadline']             ?? '';
$status      = in_array($_POST['status'] ?? '', ['open','closed']) ? $_POST['status'] : 'open';

if (!$jobId || !$periodId || $title === '' || $description === '' || $deadline === '') {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/company/edit_job.php?id=' . $jobId);
}

// Không cho deadline trong quá khứ chỉ khi user đổi sang deadline mới
// Lấy deadline hiện tại của job để so sánh
$stmtCurrent = $pdo->prepare("SELECT deadline FROM jobs WHERE id = ? AND company_id = ?");
$stmtCurrent->execute([$jobId, $company['id']]);
$currentJob = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
$currentDeadline = $currentJob['deadline'] ?? '';

if ($status === 'open' && $deadline !== $currentDeadline && $deadline <= date('Y-m-d')) {
    setFlash('error', 'Deadline must be a future date when job status is Open.');
    redirect('/Uniworksmohinhhoa/company/edit_job.php?id=' . $jobId);
}

// Nếu deadline đã qua → tự đặt status = closed
if ($deadline < date('Y-m-d')) {
    $status = 'closed';
}

try {
    $stmt = $pdo->prepare("
        UPDATE jobs
        SET period_id = ?, title = ?, description = ?, requirements = ?, slots = ?, deadline = ?, status = ?
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([$periodId, $title, $description, $requirements, $slots, $deadline, $status, $jobId, $company['id']]);

    setFlash('success', 'Job updated successfully.');
    redirect('/Uniworksmohinhhoa/company/manage_job.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to update job.');
    redirect('/Uniworksmohinhhoa/company/edit_job.php?id=' . $jobId);
}
