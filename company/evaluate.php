<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/*
|--------------------------------------------------------------------------
| PREVIEW MODE
|--------------------------------------------------------------------------
| true  = xem giao diện ngay, không cần login/db đủ dữ liệu
| false = chạy thật với session + database
*/
$previewMode = true;

function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

if ($previewMode) {
    $data = [
        'application_id' => 1,
        'status' => 'approved',
        'student_code' => 'SE001',
        'student_name' => 'Thị Trâm Nguyễn Kiều',
        'job_title' => 'Software Engineer Intern',
        'registration_id' => 1
    ];

    $evaluation = [
        'score' => '92.50',
        'feedback' => "Strong technical foundation and good communication.\nShows initiative and learns quickly."
    ];

    $success = null;
    $error = null;
} else {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $user = $_SESSION['user'];
    $appId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($appId <= 0) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Invalid application id.');
        }
        safeRedirect('applications.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Company profile not found.');
        }
        safeRedirect('../public/login.php');
    }

    $stmt = $pdo->prepare("
        SELECT a.id AS application_id, a.status,
               s.student_code,
               u.full_name AS student_name,
               j.title AS job_title,
               ir.id AS registration_id
        FROM applications a
        JOIN students s ON a.student_id = s.id
        JOIN users u ON s.user_id = u.id
        JOIN jobs j ON a.job_id = j.id
        LEFT JOIN internship_registrations ir ON ir.application_id = a.id
        WHERE a.id = ? AND j.company_id = ?
    ");
    $stmt->execute([$appId, $company['id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Application not found.');
        }
        safeRedirect('applications.php');
    }

    $evaluation = null;
    if (!empty($data['registration_id'])) {
        $stmt = $pdo->prepare("
            SELECT *
            FROM evaluations
            WHERE registration_id = ? AND evaluator_role = 'company'
            LIMIT 1
        ");
        $stmt->execute([$data['registration_id']]);
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluate Candidate</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <h2>Uniworks</h2>
                <p>Recruiter Portal</p>
            </div>

            <nav class="company-nav">
                <a href="dashboard.php">Dashboard</a>
                <a class="active" href="applications.php">Applicants</a>
                <a href="manage_job.php">Jobs</a>
                <a href="messages.php">Messages</a>
                <a href="profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-signout">
            <a href="../public/logout.php">Sign Out</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="topbar">
            <div></div>
            <div class="topbar-actions">
                <a class="btn btn-primary" href="applications.php">Back to Applicants</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1 class="page-title">Evaluate Candidate</h1>
        <p class="page-subtitle">Write your assessment for this student.</p>

        <div class="card">
            <p style="margin-bottom:10px;"><strong>Student:</strong> <?php echo htmlspecialchars($data['student_name']); ?></p>
            <p style="margin-bottom:10px;"><strong>Student Code:</strong> <?php echo htmlspecialchars($data['student_code']); ?></p>
            <p style="margin-bottom:18px;"><strong>Position:</strong> <?php echo htmlspecialchars($data['job_title']); ?></p>

            <?php if (empty($data['registration_id'])): ?>
                <div class="flash error">This candidate cannot be evaluated yet. Please approve the application first.</div>
            <?php else: ?>
                <form action="../actions/company/evaluate_action.php" method="POST">
                    <input type="hidden" name="registration_id" value="<?php echo htmlspecialchars($data['registration_id']); ?>">
                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($data['application_id']); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Score</label>
                            <input
                                type="number"
                                name="score"
                                min="0"
                                max="100"
                                step="0.01"
                                value="<?php echo htmlspecialchars($evaluation['score'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="form-group full">
                            <label>Feedback</label>
                            <textarea name="feedback" required><?php echo htmlspecialchars($evaluation['feedback'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <?php if ($previewMode): ?>
                        <button class="btn btn-primary" type="button">Save Evaluation</button>
                    <?php else: ?>
                        <button class="btn btn-primary" type="submit">Save Evaluation</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>