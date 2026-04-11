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
    $job = [
        'id' => 1,
        'period_id' => 1,
        'title' => 'Software Engineer Intern',
        'slots' => 3,
        'deadline' => '2026-05-30',
        'status' => 'open',
        'description' => "Support the development team in building and testing web features.\nCollaborate with mentors on real company projects.",
        'requirements' => "Basic knowledge of HTML, CSS, JavaScript, PHP.\nGood teamwork and communication skills."
    ];

    $periods = [
        [
            'id' => 1,
            'name' => 'Summer 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31'
        ],
        [
            'id' => 2,
            'name' => 'Fall 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-31'
        ]
    ];

    $success = null;
    $error = null;
} else {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $user = $_SESSION['user'];
    $jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($jobId <= 0) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Invalid job id.');
        }
        safeRedirect('manage_jobs.php');
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
        SELECT *
        FROM jobs
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([$jobId, $company['id']]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Job not found.');
        }
        safeRedirect('manage_jobs.php');
    }

    $stmt = $pdo->query("SELECT * FROM internship_periods ORDER BY start_date DESC");
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Job</title>
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
                <a href="applications.php">Applicants</a>
                <a class="active" href="manage_job.php">Jobs</a>
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
                <a class="btn btn-primary" href="manage_jobs.php">Back to Jobs</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1 class="page-title">Edit Job</h1>
        <p class="page-subtitle">Update internship information.</p>

        <div class="card">
            <form action="../actions/company/update_job_action.php" method="POST">
                <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Internship Period</label>
                        <select name="period_id" required>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo htmlspecialchars($period['id']); ?>"
                                    <?php echo ((int)$period['id'] === (int)$job['period_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($period['name'] . ' (' . $period['start_date'] . ' - ' . $period['end_date'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Slots</label>
                        <input type="number" name="slots" min="1" value="<?php echo (int)$job['slots']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="date" name="deadline" value="<?php echo htmlspecialchars($job['deadline']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="open" <?php echo (($job['status'] ?? '') === 'open') ? 'selected' : ''; ?>>Open</option>
                            <option value="closed" <?php echo (($job['status'] ?? '') === 'closed') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description" required><?php echo htmlspecialchars($job['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full">
                        <label>Requirements</label>
                        <textarea name="requirements"><?php echo htmlspecialchars($job['requirements'] ?? ''); ?></textarea>
                    </div>
                </div>

                <?php if ($previewMode): ?>
                    <button class="btn btn-primary" type="button">Update Job</button>
                <?php else: ?>
                    <button class="btn btn-primary" type="submit">Update Job</button>
                <?php endif; ?>
            </form>
        </div>
    </main>
</div>
</body>
</html>