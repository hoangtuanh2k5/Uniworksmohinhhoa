<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'student') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$job_id = (int)($_POST['job_id'] ?? 0);

if ($job_id <= 0) {
    setFlash('error', 'Invalid job.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    setFlash('error', 'Please complete your student profile first.');
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

$stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND status = 'open'");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    setFlash('error', 'Job not found or closed.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

$stmt = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND job_id = ?");
$stmt->execute([$student['id'], $job_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    setFlash('error', 'You have already applied for this job.');
    redirect('/Uniworksmohinhhoa/student/job_detail.php?id=' . $job_id);
}

if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
    setFlash('error', 'Please upload your CV.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

$file = $_FILES['cv_file'];
$allowed_extensions = ['pdf', 'doc', 'docx'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowed_extensions)) {
    setFlash('error', 'Invalid file type. Only PDF, DOC, DOCX allowed.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

if ($file['size'] > 5 * 1024 * 1024) {
    setFlash('error', 'File is too large. Maximum 5MB.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

$upload_dir = '../../uploads/cvs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$new_filename = 'cv_' . $student['id'] . '_' . time() . '.' . $extension;
$target_path = $upload_dir . $new_filename;
$db_path = 'uploads/cvs/' . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    setFlash('error', 'Failed to upload CV.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (student_id, job_id, cv_url, status, admin_approved)
        VALUES (?, ?, ?, 'pending', 0)
    ");
    $stmt->execute([$student['id'], $job_id, $db_path]);

    setFlash('success', 'Application submitted successfully.');
    redirect('/Uniworksmohinhhoa/student/applications.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to submit application.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}