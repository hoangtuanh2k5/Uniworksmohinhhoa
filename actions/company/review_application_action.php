<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$userId = $_SESSION['user']['id'];
$applicationId = (int)($_POST['application_id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');

$allowed = ['pending', 'reviewed', 'approved', 'rejected'];
if (!$applicationId || !in_array($status, $allowed, true)) {
    setFlash('error', 'Invalid application update.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$userId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company not found.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT a.id, j.period_id
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.id = ? AND j.company_id = ?
    ");
    $stmt->execute([$applicationId, $company['id']]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        setFlash('error', 'Application not found.');
        redirect('/Uniworksmohinhhoa/company/applications.php');
    }

    $stmt = $pdo->prepare("
        UPDATE applications a
        JOIN jobs j ON a.job_id = j.id
        SET a.status = ?
        WHERE a.id = ? AND j.company_id = ?
    ");
    $stmt->execute([$status, $applicationId, $company['id']]);

    if ($status === 'approved') {
        $stmt = $pdo->prepare("SELECT id FROM internship_registrations WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $registration = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registration) {
            $stmt = $pdo->prepare("
                SELECT start_date, end_date
                FROM internship_periods
                WHERE id = ?
            ");
            $stmt->execute([$application['period_id']]);
            $period = $stmt->fetch(PDO::FETCH_ASSOC);

            $startDate = $period ? $period['start_date'] : null;
            $endDate = $period ? $period['end_date'] : null;

            $stmt = $pdo->prepare("
                INSERT INTO internship_registrations (application_id, start_date, end_date)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$applicationId, $startDate, $endDate]);
        }
    }

    setFlash('success', 'Application status updated successfully.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
} catch (Exception $e) {
    setFlash('error', 'Failed to update application.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}