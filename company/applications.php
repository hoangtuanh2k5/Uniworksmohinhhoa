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

$statusFilter = $_GET['status'] ?? 'all';

if ($previewMode) {
    $company = [
        'id' => 1,
        'company_name' => 'Uniworks'
    ];

    $totalApplications = 1284;
    $newToday = 48;
    $inReview = 156;
    $approved = 22;

    $applications = [
        [
            'id' => 1,
            'status' => 'pending',
            'applied_at' => '2026-04-11 08:30:00',
            'cv_url' => '#',
            'admin_approved' => 0,
            'student_id' => 1,
            'student_code' => 'SE001',
            'class_name' => 'IS01',
            'gpa' => '3.92',
            'student_user_id' => 101,
            'student_name' => 'Thị Trâm Nguyễn Kiều',
            'student_email' => 'alex.j@example.com',
            'major_name' => 'Information Systems',
            'job_title' => 'Software Engineer Intern'
        ],
        [
            'id' => 2,
            'status' => 'reviewed',
            'applied_at' => '2026-04-10 10:15:00',
            'cv_url' => '#',
            'admin_approved' => 0,
            'student_id' => 2,
            'student_code' => 'SE002',
            'class_name' => 'IS02',
            'gpa' => '4.00',
            'student_user_id' => 102,
            'student_name' => 'Thị Hạnh Hồng Nguyễn',
            'student_email' => 'm.garcia@example.com',
            'major_name' => 'Computer Science',
            'job_title' => 'UI/UX Design Intern'
        ],
        [
            'id' => 3,
            'status' => 'approved',
            'applied_at' => '2026-04-09 09:00:00',
            'cv_url' => '#',
            'admin_approved' => 1,
            'student_id' => 3,
            'student_code' => 'SE003',
            'class_name' => 'IS03',
            'gpa' => '3.75',
            'student_user_id' => 103,
            'student_name' => 'Anh Tú Hoàng Kim',
            'student_email' => 'slee@example.com',
            'major_name' => 'Business Administration',
            'job_title' => 'Marketing Intern'
        ],
        [
            'id' => 4,
            'status' => 'rejected',
            'applied_at' => '2026-04-08 14:20:00',
            'cv_url' => '#',
            'admin_approved' => 0,
            'student_id' => 4,
            'student_code' => 'SE004',
            'class_name' => 'IS04',
            'gpa' => '3.58',
            'student_user_id' => 104,
            'student_name' => 'Thị Lê Yến',
            'student_email' => 'jordan@example.com',
            'major_name' => 'Information Systems',
            'job_title' => 'Business Analyst Intern'
        ],
        [
            'id' => 5,
            'status' => 'reviewed',
            'applied_at' => '2026-04-07 16:40:00',
            'cv_url' => '#',
            'admin_approved' => 0,
            'student_id' => 5,
            'student_code' => 'SE005',
            'class_name' => 'IS05',
            'gpa' => '3.88',
            'student_user_id' => 105,
            'student_name' => 'Tôi yêu bạn',
            'student_email' => 'riley@example.com',
            'major_name' => 'Computer Science',
            'job_title' => 'Data Analyst Intern'
        ]
    ];

    if ($statusFilter !== 'all') {
        $applications = array_values(array_filter($applications, function ($item) use ($statusFilter) {
            return $item['status'] === $statusFilter;
        }));
    }

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

    $sql = "
        SELECT a.id, a.status, a.applied_at, a.cv_url, a.admin_approved,
               s.id AS student_id, s.student_code, s.class_name, s.gpa,
               u.id AS student_user_id, u.full_name AS student_name, u.email AS student_email,
               m.name AS major_name,
               j.title AS job_title
        FROM applications a
        JOIN students s ON a.student_id = s.id
        JOIN users u ON s.user_id = u.id
        LEFT JOIN majors m ON s.major_id = m.id
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ?
    ";

    $params = [$company['id']];

    if ($statusFilter !== 'all') {
        $sql .= " AND a.status = ?";
        $params[] = $statusFilter;
    }

    $sql .= " ORDER BY a.applied_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ?
    ");
    $stmt->execute([$company['id']]);
    $totalApplications = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ? AND DATE(a.applied_at) = CURDATE()
    ");
    $stmt->execute([$company['id']]);
    $newToday = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ? AND a.status = 'reviewed'
    ");
    $stmt->execute([$company['id']]);
    $inReview = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ? AND a.status = 'approved'
    ");
    $stmt->execute([$company['id']]);
    $approved = (int)$stmt->fetchColumn();

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applications</title>
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
            <div class="search-box">
                <input type="text" placeholder="Search for applicants, skills, or schools..." disabled>
            </div>

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

        <h1 class="page-title">Student Applicants</h1>
        <p class="page-subtitle">Review and manage recent applications from top university talent across the country.</p>

        <div class="stats-4">
            <div class="stat-card purple">
                <h4>Total Applications</h4>
                <div class="stat-value"><?php echo $totalApplications; ?></div>
            </div>

            <div class="stat-card yellow">
                <h4>New Today</h4>
                <div class="stat-value"><?php echo $newToday; ?></div>
            </div>

            <div class="stat-card purple">
                <h4>In Review</h4>
                <div class="stat-value"><?php echo $inReview; ?></div>
            </div>

            <div class="stat-card yellow">
                <h4>Approved</h4>
                <div class="stat-value"><?php echo $approved; ?></div>
            </div>
        </div>

        <div class="card">
            <div class="tabs">
                <a class="<?php echo $statusFilter === 'all' ? 'active' : ''; ?>" href="applications.php?status=all">All Applicants</a>
                <a class="<?php echo $statusFilter === 'pending' ? 'active' : ''; ?>" href="applications.php?status=pending">Pending</a>
                <a class="<?php echo $statusFilter === 'reviewed' ? 'active' : ''; ?>" href="applications.php?status=reviewed">Reviewed</a>
                <a class="<?php echo $statusFilter === 'approved' ? 'active' : ''; ?>" href="applications.php?status=approved">Approved</a>
                <a class="<?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>" href="applications.php?status=rejected">Rejected</a>
            </div>

            <div class="table-wrap">
                <table class="company-table">
                    <thead>
                        <tr>
                            <th>Applicant Name</th>
                            <th>Major</th>
                            <th>GPA</th>
                            <th>Status</th>
                            <th>Applied For</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="6">No applications found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <div class="applicant-cell">
                                            <div class="avatar"><?php echo htmlspecialchars(makeInitials($app['student_name'])); ?></div>
                                            <div class="applicant-meta">
                                                <strong><?php echo htmlspecialchars($app['student_name']); ?></strong>
                                                <span><?php echo htmlspecialchars($app['student_email']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['major_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($app['gpa'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo badgeClass($app['status']); ?>">
                                            <?php echo htmlspecialchars(formatAppStatus($app['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn btn-outline btn-sm" href="candidate_detail.php?id=<?php echo $app['id']; ?>">View Profile</a>
                                            <a class="btn btn-outline btn-sm" href="evaluate.php?id=<?php echo $app['id']; ?>">Evaluate</a>
                                            <a class="btn btn-outline btn-sm" href="messages.php?student_user_id=<?php echo $app['student_user_id']; ?>">Message</a>
                                        </div>

                                        <?php if (!$previewMode): ?>
                                            <div class="actions" style="margin-top:8px;">
                                                <form action="../actions/company/review_application_action.php" method="POST">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                    <input type="hidden" name="status" value="reviewed">
                                                    <button class="btn btn-outline btn-sm" type="submit">Review</button>
                                                </form>

                                                <form action="../actions/company/review_application_action.php" method="POST">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                    <input type="hidden" name="status" value="approved">
                                                    <button class="btn btn-outline btn-sm" type="submit">Approve</button>
                                                </form>

                                                <form action="../actions/company/review_application_action.php" method="POST">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="btn btn-outline btn-sm" type="submit">Reject</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
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