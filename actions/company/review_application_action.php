<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

$userId        = $_SESSION['user']['id'];
$applicationId = (int)($_POST['application_id'] ?? 0);
$decision      = $_POST['decision'] ?? '';

if ($applicationId <= 0 || !in_array($decision, ['accept', 'reject'], true)) {
    setFlash('error', 'Invalid request.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

// Lấy company
$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$userId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company not found.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

// Lấy application — phải thuộc job của company này và đã được admin approve
$stmt = $pdo->prepare("
    SELECT a.id, a.admin_approved, a.company_approved, j.period_id
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ? AND j.company_id = ?
");
$stmt->execute([$applicationId, $company['id']]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    setFlash('error', 'Application not found or access denied.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

if ((int)$application['admin_approved'] !== 1) {
    setFlash('error', 'This application has not been approved by school yet.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

if ((int)$application['company_approved'] !== 0) {
    setFlash('error', 'This application has already been reviewed.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

try {
    $pdo->beginTransaction();

    if ($decision === 'accept') {
        $pdo->prepare("
            UPDATE applications SET company_approved = 1, status = 'approved' WHERE id = ?
        ")->execute([$applicationId]);

        // Tạo internship registration nếu chưa có
        $existing = $pdo->prepare("SELECT id FROM internship_registrations WHERE application_id = ?");
        $existing->execute([$applicationId]);

        if (!$existing->fetch()) {
            $period = $pdo->prepare("SELECT start_date, end_date FROM internship_periods WHERE id = ?");
            $period->execute([$application['period_id']]);
            $p = $period->fetch(PDO::FETCH_ASSOC);

            $pdo->prepare("
                INSERT INTO internship_registrations (application_id, start_date, end_date, status)
                VALUES (?, ?, ?, 'ongoing')
            ")->execute([$applicationId, $p['start_date'] ?? null, $p['end_date'] ?? null]);
        }

        $pdo->commit();
        setFlash('success', 'Candidate accepted. Internship registration created.');
    } else {
        $pdo->prepare("
            UPDATE applications SET company_approved = -1, status = 'rejected' WHERE id = ?
        ")->execute([$applicationId]);

        $pdo->commit();
        setFlash('success', 'Candidate rejected.');
    }
} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('error', 'Failed to update application.');
}

redirect('/Uniworksmohinhhoa/company/applications.php');
