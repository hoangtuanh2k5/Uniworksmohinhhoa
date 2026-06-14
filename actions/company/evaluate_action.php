<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/functions.php';

<<<<<<< Updated upstream
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../company/applications.php');
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'company') {
    redirect('../../public/login.php');
}

$registrationId = (int)($_POST['registration_id'] ?? 0);
$applicationId = (int)($_POST['application_id'] ?? 0);
$score = (float)($_POST['score'] ?? 0);
$feedback = trim($_POST['feedback'] ?? '');

if (!$registrationId || !$applicationId || $feedback === '' || $score < 0 || $score > 100) {
    setFlash('error', 'Please enter valid evaluation information.');
    redirect('../../company/evaluate.php?id=' . $applicationId);
=======
$user            = currentUser();
$registration_id = (int)($_POST['registration_id'] ?? 0);
$score           = trim($_POST['score'] ?? '');
$feedback        = trim($_POST['feedback'] ?? '');

if ($registration_id <= 0 || $score === '' || $feedback === '') {
    setFlash('error', 'Please complete all evaluation fields.');
    redirect('/Uniworksmohinhhoa/company/evaluations.php');
}

if (!is_numeric($score) || (float)$score < 0 || (float)$score > 10) {
    setFlash('error', 'Score must be between 0 and 10.');
    redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
}

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company profile not found.');
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
>>>>>>> Stashed changes
}

try {
    $stmt = $pdo->prepare("
<<<<<<< Updated upstream
        SELECT id
        FROM evaluations
        WHERE registration_id = ? AND evaluator_role = 'company'
        LIMIT 1
    ");
    $stmt->execute([$registrationId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE evaluations
            SET score = ?, feedback = ?
            WHERE id = ?
        ");
        $stmt->execute([$score, $feedback, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO evaluations (registration_id, evaluator_role, score, feedback, created_at)
            VALUES (?, 'company', ?, ?, NOW())
        ");
        $stmt->execute([$registrationId, $score, $feedback]);
    }

    setFlash('success', 'Evaluation saved successfully.');
    redirect('../../company/evaluate.php?id=' . $applicationId);
} catch (Exception $e) {
    setFlash('error', 'Failed to save evaluation.');
    redirect('../../company/evaluate.php?id=' . $applicationId);
}
=======
        SELECT ir.id, ir.status
        FROM internship_registrations ir
        INNER JOIN applications a ON ir.application_id = a.id
        INNER JOIN jobs j ON a.job_id = j.id
        WHERE ir.id = ? AND j.company_id = ?
    ");
    $stmt->execute([$registration_id, $company['id']]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        setFlash('error', 'Internship registration not found or access denied.');
        redirect('/Uniworksmohinhhoa/company/evaluations.php');
    }

    if ($registration['status'] !== 'completed') {
        setFlash('error', 'You can only evaluate after the internship is completed.');
        redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
    }

    $stmt = $pdo->prepare("SELECT id FROM evaluations WHERE registration_id = ?");
    $stmt->execute([$registration_id]);
    if ($stmt->fetch()) {
        setFlash('error', 'Evaluation already exists for this internship.');
        redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
    }

    // Xử lý upload file đính kèm (tuỳ chọn)
    $file_url = null;
    if (!empty($_FILES['eval_file']['name'])) {
        $file    = $_FILES['eval_file'];
        $allowed = ['application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg', 'image/png'];
        $maxSize = 10 * 1024 * 1024;

        if (!in_array($file['type'], $allowed)) {
            setFlash('error', 'Only PDF, DOC, DOCX, JPG, PNG files are allowed.');
            redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
        }
        if ($file['size'] > $maxSize) {
            setFlash('error', 'File must be under 10MB.');
            redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'eval_' . $company['id'] . '_' . $registration_id . '_' . time() . '.' . $ext;
        $dest     = __DIR__ . '/../../uploads/evaluations/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            setFlash('error', 'File upload failed. Please try again.');
            redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
        }
        $file_url = 'uploads/evaluations/' . $filename;
    }

    $pdo->prepare("
        INSERT INTO evaluations (registration_id, evaluator_role, score, feedback, file_url, created_at)
        VALUES (?, 'company', ?, ?, ?, NOW())
    ")->execute([$registration_id, $score, $feedback, $file_url]);

    setFlash('success', 'Evaluation submitted successfully.');
    redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);

} catch (Exception $e) {
    setFlash('error', 'Failed to submit evaluation: ' . $e->getMessage());
    redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
}
>>>>>>> Stashed changes
