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
| true  = xem giao diện ngay, không cần login/db
| false = chạy thật với session + database
*/
$previewMode = true;

function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

if ($previewMode) {
    $jobs = [
        [
            'id' => 1,
            'title' => 'Software Engineer Intern',
            'period_name' => 'Summer 2026',
            'slots' => 3,
            'deadline' => '2026-05-30',
            'status' => 'open',
            'total_applications' => 24
        ],
        [
            'id' => 2,
            'title' => 'UI/UX Design Intern',
            'period_name' => 'Summer 2026',
            'slots' => 2,
            'deadline' => '2026-05-25',
            'status' => 'open',
            'total_applications' => 18
        ],
        [
            'id' => 3,
            'title' => 'Marketing Intern',
            'period_name' => 'Fall 2026',
            'slots' => 4,
            'deadline' => '2026-08-20',
            'status' => 'closed',
            'total_applications' => 42
        ],
        [
            'id' => 4,
            'title' => 'Business Analyst Intern',
            'period_name' => 'Fall 2026',
            'slots' => 2,
            'deadline' => '2026-08-10',
            'status' => 'open',
            'total_applications' => 15
        ]
    ];

    $success = null;
    $error = null;
} else {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $user = $_SESSION['user'];

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
        SELECT j.*, p.name AS period_name,
               (
                 SELECT COUNT(*)
                 FROM applications a
                 WHERE a.job_id = j.id
               ) AS total_applications
        FROM jobs j
        JOIN internship_periods p ON j.period_id = p.id
        WHERE j.company_id = ?
        ORDER BY j.id DESC
    ");
    $stmt->execute([$company['id']]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}

$totalJobs = count($jobs);
$openJobs = 0;
$closedJobs = 0;
$totalApplications = 0;

foreach ($jobs as $job) {
    if (($job['status'] ?? '') === 'open') {
        $openJobs++;
    } else {
        $closedJobs++;
    }
    $totalApplications += (int)($job['total_applications'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Jobs</title>
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
                <a class="btn btn-primary" href="create_job.php">+ Create Job</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1 class="page-title">Manage Jobs</h1>
        <p class="page-subtitle">View, edit, and manage all internship postings in one place.</p>

        <div class="stats-3">
            <div class="stat-card yellow">
                <span class="stat-pill">Current</span>
                <h4>Total Jobs</h4>
                <div class="stat-value"><?php echo $totalJobs; ?></div>
            </div>

            <div class="stat-card purple">
                <span class="stat-pill">Active</span>
                <h4>Open Jobs</h4>
                <div class="stat-value"><?php echo $openJobs; ?></div>
            </div>

            <div class="stat-card yellow">
                <span class="stat-pill">Hiring</span>
                <h4>Total Applications</h4>
                <div class="stat-value"><?php echo $totalApplications; ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Job Listings</h3>
                    <p>Track deadlines, slots, and student interest for each role.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="company-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Period</th>
                            <th>Slots</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Applications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr>
                                <td colspan="7">No jobs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['period_name']); ?></td>
                                    <td><?php echo (int)$job['slots']; ?></td>
                                    <td><?php echo htmlspecialchars($job['deadline']); ?></td>
                                    <td>
                                        <span class="badge <?php echo (($job['status'] ?? '') === 'open') ? 'approved' : 'reviewed'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($job['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo (int)$job['total_applications']; ?></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn btn-outline btn-sm" href="edit_job.php<?php echo $previewMode ? '' : '?id=' . $job['id']; ?>">Edit</a>

                                            <?php if ($previewMode): ?>
                                                <button class="btn btn-outline btn-sm" type="button">Delete</button>
                                            <?php else: ?>
                                                <form action="../actions/company/delete_job_action.php" method="POST" onsubmit="return confirm('Delete this job?');">
                                                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                                                    <button class="btn btn-outline btn-sm" type="submit">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>