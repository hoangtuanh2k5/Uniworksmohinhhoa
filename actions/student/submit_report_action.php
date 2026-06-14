<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireRole('student');

$user            = currentUser();
$registration_id = (int)($_POST['registration_id'] ?? 0);
$content         = trim($_POST['content'] ?? '');

if ($registration_id <= 0 || $content === '') {
    setFlash('error', 'Please complete the report form.');
    redirect('/Uniworksmohinhhoa/student/report.php');
}

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    setFlash('error', 'Student profile not found.');
    redirect('/Uniworksmohinhhoa/student/report.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT ir.id, ir.status
        FROM internship_registrations ir
        INNER JOIN applications a ON ir.application_id = a.id
        WHERE ir.id = ? AND a.student_id = ?
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

    $stmt = $pdo->prepare("SELECT id FROM reports WHERE registration_id = ?");
    $stmt->execute([$registration_id]);
    if ($stmt->fetch()) {
        setFlash('error', 'You have already submitted a report for this internship.');
        redirect('/Uniworksmohinhhoa/student/report.php');
    }

    // Xử lý upload file (tuỳ chọn)
    $file_url = null;
    if (!empty($_FILES['report_file']['name'])) {
        $file   = $_FILES['report_file'];
        $allowed = ['application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024;

        if (!in_array($file['type'], $allowed)) {
            setFlash('error', 'Only PDF, DOC, DOCX files are allowed for the report.');
            redirect('/Uniworksmohinhhoa/student/report.php');
        }
        if ($file['size'] > $maxSize) {
            setFlash('error', 'File must be under 10MB.');
            redirect('/Uniworksmohinhhoa/student/report.php');
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'report_' . $student['id'] . '_' . time() . '.' . $ext;
        $dest     = __DIR__ . '/../../uploads/reports/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            setFlash('error', 'File upload failed. Please try again.');
            redirect('/Uniworksmohinhhoa/student/report.php');
        }
        $file_url = 'uploads/reports/' . $filename;
    }

    $pdo->prepare("
        INSERT INTO reports (registration_id, content, file_url, submitted_at)
        VALUES (?, ?, ?, NOW())
    ")->execute([$registration_id, $content, $file_url]);

    setFlash('success', 'Final report submitted successfully.');

} catch (Exception $e) {
    setFlash('error', 'Failed to submit report: ' . $e->getMessage());
}

redirect('/Uniworksmohinhhoa/student/report.php');
