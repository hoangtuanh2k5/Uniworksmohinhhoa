<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/functions.php';

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
}

try {
    $stmt = $pdo->prepare("
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