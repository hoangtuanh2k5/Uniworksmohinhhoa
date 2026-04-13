<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];

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

if (!$title || $period_id <= 0 || !$description || !$requirements || $slots <= 0 || !$deadline) {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/company/create_job.php');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO jobs (company_id, period_id, title, description, requirements, slots, deadline, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'open')
    ");
    $stmt->execute([$company['id'], $period_id, $title, $description, $requirements, $slots, $deadline]);

    setFlash('success', 'Job created successfully.');
    redirect('/Uniworksmohinhhoa/company/manage_job.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to create job.');
    redirect('/Uniworksmohinhhoa/company/create_job.php');
}