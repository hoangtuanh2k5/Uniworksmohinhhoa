<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
requireRole('company');

$user = currentUser();

$application_id = (int)($_POST['application_id'] ?? 0);
$decision = $_POST['decision'] ?? '';

if ($application_id <= 0 || !in_array($decision, ['accept', 'reject'], true)) {
    setFlash('error', 'Invalid request.');
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

/*
|-------------------------------------------------------
| Kiểm tra application thuộc job của company này
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.job_id,
        a.admin_approved,
        a.company_approved,
        a.status
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ?
      AND j.company_id = ?
");
$stmt->execute([$application_id, $company['id']]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    setFlash('error', 'Application not found or access denied.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

/*
|-------------------------------------------------------
| Chỉ được duyệt khi school/admin đã approve
|-------------------------------------------------------
*/
if ((int)$application['admin_approved'] !== 1) {
    setFlash('error', 'This application has not been approved by school yet.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

/*
|-------------------------------------------------------
| Không cho company duyệt lại
|-------------------------------------------------------
*/
if ((int)$application['company_approved'] !== 0) {
    setFlash('error', 'This application has already been reviewed.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

try {
    $pdo->beginTransaction();

    if ($decision === 'accept') {
        /*
        |---------------------------------------------------
        | Company accept
        | status của applications giữ là approved
        |---------------------------------------------------
        */
        $stmt = $pdo->prepare("
            UPDATE applications
            SET company_approved = 1,
                status = 'approved'
            WHERE id = ?
        ");
        $stmt->execute([$application_id]);

        /*
        |---------------------------------------------------
        | Tạo internship registration
        | start_date = hôm nay
        | end_date = sau 3 tháng
        | status = ongoing
        |---------------------------------------------------
        */
        $stmt = $pdo->prepare("
            SELECT id
            FROM internship_registrations
            WHERE application_id = ?
        ");
        $stmt->execute([$application_id]);
        $existingRegistration = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingRegistration) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+3 months'));

            $stmt = $pdo->prepare("
                INSERT INTO internship_registrations (application_id, start_date, end_date, status)
                VALUES (?, ?, ?, 'ongoing')
            ");
            $stmt->execute([$application_id, $startDate, $endDate]);
        }

        $pdo->commit();
        setFlash('success', 'Application accepted successfully. Internship has started.');
    } else {
        /*
        |---------------------------------------------------
        | Company reject
        |---------------------------------------------------
        */
        $stmt = $pdo->prepare("
            UPDATE applications
            SET company_approved = -1,
                status = 'approved'
            WHERE id = ?
        ");
        $stmt->execute([$application_id]);

        $pdo->commit();
        setFlash('success', 'Application rejected successfully.');
    }

} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('error', 'Failed to review application: ' . $e->getMessage());
}

redirect('/Uniworksmohinhhoa/company/applications.php');