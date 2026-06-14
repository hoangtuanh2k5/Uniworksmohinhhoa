<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'student') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

$user_id = $_SESSION['user']['id'];
$job_id  = (int)($_POST['job_id'] ?? 0);

if ($job_id <= 0) {
    setFlash('error', 'Invalid job.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

// Lấy student record
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    setFlash('error', 'Please complete your profile first.');
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

// Chặn nếu đã có internship completed (admin đã mark hoàn thành)
$stmt = $pdo->prepare("
    SELECT ir.id FROM internship_registrations ir
    INNER JOIN applications a ON ir.application_id = a.id
    WHERE a.student_id = ? AND ir.status = 'completed'
    LIMIT 1
");
$stmt->execute([$student['id']]);
if ($stmt->fetch()) {
    setFlash('error', 'You have already completed an internship and cannot apply for new jobs.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

// Kiểm tra job tồn tại và còn mở
$stmt = $pdo->prepare("SELECT id, deadline, status FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job || $job['status'] !== 'open') {
    setFlash('error', 'This job is no longer available.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

if ($job['deadline'] < date('Y-m-d')) {
    setFlash('error', 'The application deadline has passed.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

// Kiểm tra đã apply chưa
$stmt = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND job_id = ?");
$stmt->execute([$student['id'], $job_id]);
if ($stmt->fetch()) {
    setFlash('error', 'You have already applied for this job.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

// Xử lý upload CV
if (empty($_FILES['cv_file']['name'])) {
    setFlash('error', 'Please upload your CV.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

$file    = $_FILES['cv_file'];
$allowed = ['application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed)) {
    setFlash('error', 'Only PDF, DOC, DOCX files are allowed.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

if ($file['size'] > $maxSize) {
    setFlash('error', 'CV file must be under 5MB.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'cv_' . $user_id . '_' . time() . '.' . $ext;
$dest     = __DIR__ . '/../../uploads/cvs/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    setFlash('error', 'Failed to upload CV. Please try again.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

$cv_url = 'uploads/cvs/' . $filename;

// Lưu application
try {
    $pdo->prepare("
        INSERT INTO applications (student_id, job_id, cv_url, status, admin_approved, company_approved)
        VALUES (?, ?, ?, 'pending', 0, 0)
    ")->execute([$student['id'], $job_id, $cv_url]);

    setFlash('success', 'Application submitted successfully! Waiting for school approval.');
    redirect('/Uniworksmohinhhoa/student/applications.php');

} catch (PDOException $e) {
    // Xóa file vừa upload nếu lưu DB thất bại
    @unlink($dest);
    if ($e->getCode() == 23000) {
        setFlash('error', 'You have already applied for this job.');
    } else {
        setFlash('error', 'Failed to submit application.');
    }
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}
