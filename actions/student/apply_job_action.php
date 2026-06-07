<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'student') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$job_id = (int)($_POST['job_id'] ?? 0);

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    setFlash('error', 'Please complete your profile first.');
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

if ($job_id <= 0) {
    setFlash('error', 'Invalid job.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

if (!isset($_FILES['cv_file'])) {
    die('cv_file not found in $_FILES');
}

if ($_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
    die('Upload error code: ' . $_FILES['cv_file']['error']);
}

$file = $_FILES['cv_file'];
$originalName = $file['name'];
$tmpName = $file['tmp_name'];
$fileSize = $file['size'];

$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowed = ['pdf', 'doc', 'docx'];

if (!in_array($ext, $allowed)) {
    setFlash('error', 'Only PDF, DOC, and DOCX files are allowed.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

if ($fileSize > 5 * 1024 * 1024) {
    setFlash('error', 'File size must be less than 5MB.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}

/*
|-------------------------------------------------------
| Dùng đường dẫn tuyệt đối để upload
| __DIR__ = /Uniworksmohinhhoa/actions/student
| ../../uploads/cvs = /Uniworksmohinhhoa/uploads/cvs
|-------------------------------------------------------
*/
$uploadDir = __DIR__ . '/../../uploads/cvs/';

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die('Cannot create upload directory: ' . $uploadDir);
    }
}

if (!is_writable($uploadDir)) {
    die('Upload directory is not writable: ' . $uploadDir);
}

$newFileName = 'cv_' . $student['id'] . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

if (!move_uploaded_file($tmpName, $targetPath)) {
    die('move_uploaded_file failed. Target path: ' . $targetPath);
}

$cvUrl = 'uploads/cvs/' . $newFileName;

try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (student_id, job_id, cv_url, status, admin_approved, company_approved)
        VALUES (?, ?, ?, 'pending', 0, 0)
    ");
    $stmt->execute([$student['id'], $job_id, $cvUrl]);

    setFlash('success', 'Application submitted successfully.');
    redirect('/Uniworksmohinhhoa/student/applications.php');

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        setFlash('error', 'You have already applied for this job.');
    } else {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);

} catch (Exception $e) {
    setFlash('error', 'Failed to submit application.');
    redirect('/Uniworksmohinhhoa/student/apply.php?job_id=' . $job_id);
}