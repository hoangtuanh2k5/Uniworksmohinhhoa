<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'student') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id        = $_SESSION['user']['id'];
$application_id = (int)($_POST['application_id'] ?? 0);

if ($application_id <= 0) {
    setFlash('error', 'Invalid request.');
    redirect('/Uniworksmohinhhoa/student/applications.php');
}

// Lấy student_id
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    setFlash('error', 'Student profile not found.');
    redirect('/Uniworksmohinhhoa/student/applications.php');
}

// Kiểm tra đơn thuộc về sinh viên này và còn đang pending (chưa admin duyệt)
$stmt = $pdo->prepare("
    SELECT id, admin_approved
    FROM applications
    WHERE id = ? AND student_id = ?
");
$stmt->execute([$application_id, $student['id']]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    setFlash('error', 'Application not found.');
    redirect('/Uniworksmohinhhoa/student/applications.php');
}

if ((int)$app['admin_approved'] !== 0) {
    setFlash('error', 'You can only withdraw applications that are still pending school approval.');
    redirect('/Uniworksmohinhhoa/student/applications.php');
}

// Xóa đơn
$pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$application_id]);

setFlash('success', 'Application withdrawn successfully.');
redirect('/Uniworksmohinhhoa/student/applications.php');
