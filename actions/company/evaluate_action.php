<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$application_id = (int)($_POST['application_id'] ?? 0);
$score = $_POST['score'] ?? null;
$feedback = trim($_POST['feedback'] ?? '');

if ($application_id <= 0 || $score === null || $feedback === '') {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

try {
    $stmt = $pdo->prepare("
        SELECT ir.id
        FROM internship_registrations ir
        INNER JOIN applications a ON ir.application_id = a.id
        INNER JOIN jobs j ON a.job_id = j.id
        WHERE a.id = ? AND j.company_id = ?
        LIMIT 1
    ");
    $stmt->execute([$application_id, $company['id']]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        $stmt = $pdo->prepare("
            INSERT INTO internship_registrations (application_id, start_date, end_date)
            VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH))
        ");
        $stmt->execute([$application_id]);
        $registration_id = $pdo->lastInsertId();
    } else {
        $registration_id = $registration['id'];
    }

    $stmt = $pdo->prepare("
        INSERT INTO evaluations (registration_id, evaluator_role, score, feedback, created_at)
        VALUES (?, 'company', ?, ?, NOW())
    ");
    $stmt->execute([$registration_id, $score, $feedback]);

    setFlash('success', 'Evaluation submitted successfully.');
    redirect('/Uniworksmohinhhoa/company/applications.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to submit evaluation.');
    redirect('/Uniworksmohinhhoa/company/evaluate.php?application_id=' . $application_id);
}