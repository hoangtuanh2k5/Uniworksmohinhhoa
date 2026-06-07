<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireRole('student');

$user = currentUser();

$registration_id = (int)($_POST['registration_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($registration_id <= 0 || $content === '') {
    setFlash('error', 'Please complete the report form.');
    redirect('/Uniworksmohinhhoa/student/report.php');
}

/*
|-------------------------------------------------------
| Lấy student hiện tại
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    setFlash('error', 'Student profile not found.');
    redirect('/Uniworksmohinhhoa/student/report.php');
}

try {
    /*
    |---------------------------------------------------
    | Kiểm tra registration có thuộc student này không
    |---------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT 
            ir.id,
            ir.status
        FROM internship_registrations ir
        INNER JOIN applications a ON ir.application_id = a.id
        WHERE ir.id = ?
          AND a.student_id = ?
    ");
    $stmt->execute([$registration_id, $student['id']]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        setFlash('error', 'Internship registration not found.');
        redirect('/Uniworksmohinhhoa/student/report.php');
    }

    if ($registration['status'] !== 'completed') {
        setFlash('error', 'You can only submit a report after completing the internship.');
        redirect('/Uniworksmohinhhoa/student/report.php');
    }

    /*
    |---------------------------------------------------
    | Không cho nộp trùng report
    |---------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT id
        FROM reports
        WHERE registration_id = ?
    ");
    $stmt->execute([$registration_id]);
    $existingReport = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingReport) {
        setFlash('error', 'You have already submitted a report for this internship.');
        redirect('/Uniworksmohinhhoa/student/report.php');
    }

    /*
    |---------------------------------------------------
    | Lưu report
    |---------------------------------------------------
    */
    $stmt = $pdo->prepare("
        INSERT INTO reports (registration_id, content, submitted_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$registration_id, $content]);

    setFlash('success', 'Final report submitted successfully.');

} catch (Exception $e) {
    setFlash('error', 'Failed to submit report: ' . $e->getMessage());
}

redirect('/Uniworksmohinhhoa/student/report.php');