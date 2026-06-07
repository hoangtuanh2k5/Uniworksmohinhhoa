<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireRole('company');

$user = currentUser();

$registration_id = (int)($_POST['registration_id'] ?? 0);
$score = trim($_POST['score'] ?? '');
$feedback = trim($_POST['feedback'] ?? '');

if ($registration_id <= 0 || $score === '' || $feedback === '') {
    setFlash('error', 'Please complete all evaluation fields.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

if (!is_numeric($score) || (float)$score < 0 || (float)$score > 10) {
    setFlash('error', 'Score must be between 0 and 10.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

/*
|-------------------------------------------------------
| Lấy company hiện tại
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company profile not found.');
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

try {
    /*
    |---------------------------------------------------
    | Kiểm tra registration có thuộc company này không
    |---------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT 
            ir.id,
            ir.status
        FROM internship_registrations ir
        INNER JOIN applications a ON ir.application_id = a.id
        INNER JOIN jobs j ON a.job_id = j.id
        WHERE ir.id = ?
          AND j.company_id = ?
    ");
    $stmt->execute([$registration_id, $company['id']]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        setFlash('error', 'Internship registration not found or access denied.');
        redirect('/Uniworksmohinhhoa/company/applications.php');
    }

    if ($registration['status'] !== 'completed') {
        setFlash('error', 'You can only evaluate after the internship is completed.');
        redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
    }

    /*
    |---------------------------------------------------
    | Không cho đánh giá trùng
    |---------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT id
        FROM evaluations
        WHERE registration_id = ?
    ");
    $stmt->execute([$registration_id]);
    $existingEvaluation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingEvaluation) {
        setFlash('error', 'Evaluation already exists for this internship.');
        redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
    }

    /*
    |---------------------------------------------------
    | Lưu evaluation
    |---------------------------------------------------
    */
    $stmt = $pdo->prepare("
        INSERT INTO evaluations (registration_id, score, feedback, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$registration_id, $score, $feedback]);

    setFlash('success', 'Evaluation submitted successfully.');
    redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);

} catch (Exception $e) {
    setFlash('error', 'Failed to submit evaluation: ' . $e->getMessage());
    redirect('/Uniworksmohinhhoa/company/evaluate.php?id=' . $registration_id);
}