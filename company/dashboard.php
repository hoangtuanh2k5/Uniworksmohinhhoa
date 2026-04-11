<?php
require_once '../includes/auth.php';
requireRole('company');
include '../includes/header.php';
include '../includes/navbar.php';
?>
<main class="container center-box">
    <div class="simple-card">
        <h2>Company Dashboard</h2>
    </div>
</main>
<?php include '../includes/footer.php'; ?>


<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/*
|--------------------------------------------------------------------------
| TẠM THỜI CHO XEM GIAO DIỆN NẾU CHƯA LOGIN
|--------------------------------------------------------------------------
| Nếu đã login company thì để false
| Nếu chỉ muốn xem giao diện trước thì đổi thành true
*/
$previewMode = true;

/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/
function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

function formatAppStatus($status) {
    $status = strtolower(trim((string)$status));
    if ($status === 'pending') return 'Pending';
    if ($status === 'reviewed') return 'Reviewed';
    if ($status === 'approved') return 'Approved';
    if ($status === 'rejected') return 'Rejected';
    return ucfirst($status);
}

function badgeClass($status) {
    $status = strtolower(trim((string)$status));
    if (in_array($status, ['pending', 'reviewed', 'approved', 'rejected'], true)) {
        return $status;
    }
    return 'pending';
}

function makeInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $initials = '';
    foreach ($parts as $p) {
        if ($p !== '') {
            $initials .= strtoupper(substr($p, 0, 1));
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }
    return $initials ?: 'NA';
}

/*
|--------------------------------------------------------------------------
| PREVIEW MODE: KHÔNG CẦN LOGIN / KHÔNG CẦN DB ĐỦ DATA
|--------------------------------------------------------------------------
*/
if ($previewMode) {
    $company = [
        'id' => 1,
        'company_name' => 'Uniworks'
    ];

    $totalJobs = 12;
    $totalApplicants = 1284;
    $pipelineCount = 45;
    $pendingCount = 24;
    $reviewedCount = 12;
    $rejectedCount = 3;

    $recentApplicants = [
        [
            'student_name' => 'Jane Doe Yến',
            'job_title' => 'Software Engineer Intern',
            'status' => 'reviewed',
            'applied_at' => '2023-10-24 10:00:00'
        ],
        [
            'student_name' => 'Mark Smith Tú Anh',
            'job_title' => 'UI/UX Design Intern',
            'status' => 'pending',
            'applied_at' => '2023-10-22 09:30:00'
        ],
        [
            'student_name' => 'Alex Wong Hạnh',
            'job_title' => 'Marketing Intern',
            'status' => 'approved',
            'applied_at' => '2023-10-21 14:15:00'
        ]
    ];

    $success = null;
    $error = null;
} else {
    /*
    |--------------------------------------------------------------------------
    | CHẠY THẬT
    |--------------------------------------------------------------------------
    */
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $user = $_SESSION['user'];

    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name, u.email, u.phone
        FROM companies c
        JOIN users u ON c.user_id = u.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Company profile not found.');
        }
        safeRedirect('../public/login.php');
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?");
    $stmt->execute([$company['id']]);
    $totalJobs = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ?
    ");
    $stmt->execute([$company['id']]);
    $totalApplicants = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ? AND a.status = 'approved'
    ");
    $stmt->execute([$company['id']]);
    $pipelineCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ? AND a.status = 'pending'
    ");
    $stmt->execute([$company['id']]);
    $pendingCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ? AND a.status = 'reviewed'
    ");
    $stmt->execute([$company['id']]);
    $reviewedCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ? AND a.status = 'rejected'
    ");
    $stmt->execute([$company['id']]);
    $rejectedCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT a.id, a.status, a.applied_at,
               u.full_name AS student_name,
               m.name AS major_name,
               j.title AS job_title
        FROM applications a
        JOIN students s ON a.student_id = s.id
        JOIN users u ON s.user_id = u.id
        LEFT JOIN majors m ON s.major_id = m.id
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ?
        ORDER BY a.applied_at DESC
        LIMIT 5
    ");
    $stmt->execute([$company['id']]);
    $recentApplicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Dashboard</title>
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
                <a class="active" href="dashboard.php">Dashboard</a>
                <a href="applications.php">Applicants</a>
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
                <a class="btn btn-primary" href="create_job.php">+ Post New Job</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1 class="page-title">Recruitment Overview</h1>
        <p class="page-subtitle">Track your active internships and hiring progress.</p>

        <div class="stats-3">
            <div class="stat-card yellow">
                <span class="stat-pill">+2% today</span>
                <h4>Active Internships</h4>
                <div class="stat-value"><?php echo $totalJobs; ?></div>
            </div>

            <div class="stat-card purple">
                <span class="stat-pill">+15% monthly</span>
                <h4>Total Applicants</h4>
                <div class="stat-value"><?php echo $totalApplicants; ?></div>
            </div>

            <div class="stat-card yellow">
                <span class="stat-pill">+5% week</span>
                <h4>Hiring Pipeline</h4>
                <div class="stat-value"><?php echo $pipelineCount; ?></div>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Application Trends</h3>
                        <p>Applications received across categories</p>
                    </div>
                    <button class="btn btn-outline btn-sm" type="button">Last 30 days</button>
                </div>

                <div class="chart-placeholder">
                    <div class="fake-bar" style="height: 110px;"></div>
                    <div class="fake-bar yellow" style="height: 150px;"></div>
                    <div class="fake-bar" style="height: 125px;"></div>
                    <div class="fake-bar yellow" style="height: 170px;"></div>
                    <div class="fake-bar" style="height: 95px;"></div>
                    <div class="fake-bar yellow" style="height: 130px;"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Pipeline Breakdown</h3>
                    </div>
                </div>

                <div class="pipeline-item">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Pending Review</span>
                        <strong><?php echo $pendingCount; ?></strong>
                    </div>
                    <div class="pipeline-line">
                        <div class="pipeline-fill" style="width: <?php echo $totalApplicants > 0 ? ($pendingCount / $totalApplicants) * 100 : 0; ?>%;"></div>
                    </div>
                </div>

                <div class="pipeline-item">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Reviewed</span>
                        <strong><?php echo $reviewedCount; ?></strong>
                    </div>
                    <div class="pipeline-line">
                        <div class="pipeline-fill" style="width: <?php echo $totalApplicants > 0 ? ($reviewedCount / $totalApplicants) * 100 : 0; ?>%;"></div>
                    </div>
                </div>

                <div class="pipeline-item">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Approved</span>
                        <strong><?php echo $pipelineCount; ?></strong>
                    </div>
                    <div class="pipeline-line">
                        <div class="pipeline-fill green" style="width: <?php echo $totalApplicants > 0 ? ($pipelineCount / $totalApplicants) * 100 : 0; ?>%;"></div>
                    </div>
                </div>

                <div class="pipeline-item">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Rejected</span>
                        <strong><?php echo $rejectedCount; ?></strong>
                    </div>
                    <div class="pipeline-line">
                        <div class="pipeline-fill" style="width: <?php echo $totalApplicants > 0 ? ($rejectedCount / $totalApplicants) * 100 : 0; ?>%;"></div>
                    </div>
                </div>

                <div style="margin-top:18px;">
                    <a class="btn btn-dark" href="applications.php" style="width:100%;">View Full Pipeline</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Recent Applicants</h3>
                </div>
                <a href="applications.php" style="font-size:13px;font-weight:700;color:#6e58ff;">See all →</a>
            </div>

            <div class="table-wrap">
                <table class="company-table">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Role</th>
                            <th>Stage</th>
                            <th>Date Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentApplicants)): ?>
                            <tr>
                                <td colspan="4">No applicants yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentApplicants as $row): ?>
                                <tr>
                                    <td>
                                        <div class="applicant-cell">
                                            <div class="avatar"><?php echo htmlspecialchars(makeInitials($row['student_name'])); ?></div>
                                            <div class="applicant-meta">
                                                <strong><?php echo htmlspecialchars($row['student_name']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['job_title']); ?></td>
                                    <td>
                                        <span class="badge <?php echo badgeClass($row['status']); ?>">
                                            <?php echo htmlspecialchars(formatAppStatus($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($row['applied_at']))); ?></td>
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